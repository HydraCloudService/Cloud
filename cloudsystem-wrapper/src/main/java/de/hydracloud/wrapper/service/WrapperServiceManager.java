package de.hydracloud.wrapper.service;

import de.hydracloud.api.event.service.CloudServiceUpdateEvent;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.RedirectPacket;
import de.hydracloud.api.network.packet.service.*;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.service.ServiceManager;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.wrapper.PropertyFile;
import de.hydracloud.wrapper.Wrapper;
import org.jetbrains.annotations.NotNull;

import java.util.List;

public final class WrapperServiceManager implements ServiceManager {

    private List<CloudService> allCachedServices;
    private final PropertyFile property;

    private CloudService thisService;

    public WrapperServiceManager(final PropertyFile property) {
        this.property = property;

        final var networkHandler = Wrapper.getInstance().getPacketHandler();

        networkHandler.registerPacketListener(ServiceUpdatePacket.class, (channelHandlerContext, packet) ->
            this.getService(packet.getService()).ifPresent(service -> {
                service.setState(packet.getState());
                service.setMaxPlayers(packet.getMaxPlayers());
                service.setMotd(packet.getMotd());
                Wrapper.getInstance().getEventHandler().call(new CloudServiceUpdateEvent(service));
            }));

        networkHandler.registerPacketListener(ServiceRemovePacket.class, (channelHandlerContext, packet) -> this.allCachedServices.remove(getServiceByNameOrNull(packet.getService())));
        networkHandler.registerPacketListener(ServiceAddPacket.class, (channelHandlerContext, packet) -> this.allCachedServices.add(packet.getService()));
    }

    @NotNull
    @Override
    public List<CloudService> getAllCachedServices() {
        return this.allCachedServices;
    }

    @Override
    public void setAllCachedServices(@NotNull List<CloudService> allCachedServices) {
        this.allCachedServices = allCachedServices;
        this.thisService = this.allCachedServices.stream()
            .filter(cloudService -> cloudService.getName().equalsIgnoreCase(this.property.getService())).findAny().orElse(null);
    }

    @Override
    public void startService(@NotNull CloudService group) {
        Wrapper.getInstance().getClient().sendPacket(new ServiceStartPacket(group.getName()));
    }

    public CloudService thisService() {
        return this.thisService;
    }

    @Override
    public void updateService(@NotNull CloudService service) {
        Wrapper.getInstance().getClient().sendPacket(new QueryPacket(new ServiceUpdatePacket(service), QueryPacket.QueryState.FIRST_RESPONSE));
    }

    @Override
    public void sendPacketToService(@NotNull CloudService service, @NotNull Packet packet) {
        if (service.equals(thisService())) {
            Wrapper.getInstance().getPacketHandler().call(null, packet);
            return;
        }
        Wrapper.getInstance().getClient().sendPacket(new RedirectPacket(service.getName(), packet));
    }

    @Override
    public void shutdownService(@NotNull CloudService service) {
        Wrapper.getInstance().getClient().sendPacket(new ServiceRequestShutdownPacket(service.getName()));
    }

}
