package de.hydracloud.base.node;

import de.hydracloud.base.config.CloudConfiguration;
import de.hydracloud.api.event.service.CloudServiceRemoveEvent;
import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.network.packet.init.CacheInitPacket;
import de.hydracloud.api.network.packet.service.ServiceRemovePacket;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.base.Base;
import de.hydracloud.base.service.LocalService;
import de.hydracloud.network.NetworkType;
import de.hydracloud.network.server.NettyServer;
import de.hydracloud.network.server.client.ConnectedClient;
import lombok.Getter;

@Getter
public final class BaseNode extends NettyServer {

    private final String hostName;
    private final int port;

    public BaseNode(final CloudConfiguration cloudConfiguration) {
        super(Base.getInstance().getPacketHandler(), cloudConfiguration.getNodeConfiguration().getNodeName(), NetworkType.NODE);

        this.hostName = cloudConfiguration.getNodeConfiguration().getHostname();
        this.port = cloudConfiguration.getNodeConfiguration().getPort();

        new BaseNodeNetwork();

        this.connect(this.hostName, this.port);
        Base.getInstance().getLogger().log("§7Node clustering successfully §astarted§7.");
    }

    @Override
    public void onNodeConnected(final ConnectedClient connectedClient) {
        Base.getInstance().getLogger().log("§7Node '§b" + connectedClient.name() + "§7' connected to the cluster.");
    }

    @Override
    public void onNodeDisconnected(final ConnectedClient connectedClient) {
        Base.getInstance().getLogger().log("§7Node '§b" + connectedClient.name() + "§7' disconnected from the cluster.");
    }

    @Override
    public void onServiceConnected(final ConnectedClient connectedClient) {
        if (Base.getInstance().getServiceManager().getServiceByNameOrNull(connectedClient.name()) instanceof LocalService localService) {
            localService.setState(ServiceState.STARTED);

            connectedClient.sendPacket(new CacheInitPacket(
                Base.getInstance().getGroupManager().getAllCachedServiceGroups(),
                Base.getInstance().getServiceManager().getAllCachedServices(),
                Base.getInstance().getPlayerManager().getPlayers()));

            localService.update();

            Base.getInstance().getLogger().log(
                "§7Service '§b" + connectedClient.name() + "§7' successfully §aconnected to the cluster.§7 (Startup time: §b" + (System.currentTimeMillis() - localService.getStartTime()) + "§7 ms)");
        }
    }

    @Override
    public void onServiceDisconnected(final ConnectedClient client) {
        final var base = Base.getInstance();

        base.getServiceManager().getService(client.name())
            .ifPresentOrElse(service -> {
                base.getEventHandler().call(new CloudServiceRemoveEvent(service.getName()));
                base.getNode().sendPacketToAll(new ServiceRemovePacket(service.getName()));
                base.getServiceManager().getAllCachedServices().remove(service);
                service.stop();
                base.getLogger().log("§7Service '§b" + service.getName() + "§7' has §cdisconnected§7 from the cluster.");
            }, () -> {
                base.getLogger().log("§7Service '§b" + client.name() + "§7' disconnected from the cluster, but was not found in the service manager.", LogType.WARNING);
            });
    }
}
