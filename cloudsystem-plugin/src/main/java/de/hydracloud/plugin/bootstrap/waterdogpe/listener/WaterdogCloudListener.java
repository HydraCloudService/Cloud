package de.hydracloud.plugin.bootstrap.waterdogpe.listener;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.event.service.CloudServiceRegisterEvent;
import de.hydracloud.api.event.service.CloudServiceRemoveEvent;
import de.hydracloud.api.network.packet.player.CloudPlayerKickPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerMessagePacket;
import de.hydracloud.api.network.packet.player.CloudPlayerSendServicePacket;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.network.packet.PacketListener;
import de.hydracloud.wrapper.Wrapper;
import dev.waterdog.waterdogpe.ProxyServer;
import dev.waterdog.waterdogpe.network.serverinfo.BedrockServerInfo;
import io.netty.channel.ChannelHandlerContext;
import org.jetbrains.annotations.NotNull;

import java.net.InetSocketAddress;

public class WaterdogCloudListener {

    public WaterdogCloudListener(Wrapper wrapper) {
        // load all current groups
        for (final var allCachedService : wrapper.getServiceManager().getAllCachedServices()) {
            if (!allCachedService.getGroup().getGameServerVersion().isProxy()) registerService(allCachedService);
        }

        CloudAPI.getInstance().getEventHandler().registerEvent(CloudServiceRegisterEvent.class, event -> {
            if (!event.getService().getGroup().getGameServerVersion().isProxy())
                registerService(event.getService());
        });

        CloudAPI.getInstance().getEventHandler().registerEvent(CloudServiceRemoveEvent.class, event -> {
            unregisterService(event.getService());
        });
    }

    private void registerService(String name, InetSocketAddress socketAddress) {
        ProxyServer.getInstance().registerServerInfo(new BedrockServerInfo(name, socketAddress, null));
    }

    public void unregisterService(String name) {
        ProxyServer.getInstance().getServerInfoMap().remove(name);
    }

    public void registerService(@NotNull CloudService service) {
        this.registerService(service.getName(), new InetSocketAddress(service.getHostName(), service.getPort()));
    }
}
