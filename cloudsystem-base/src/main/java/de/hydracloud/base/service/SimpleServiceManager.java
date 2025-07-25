package de.hydracloud.base.service;

import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.network.packet.service.*;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.base.Base;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.event.service.CloudServiceUpdateEvent;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.ResponsePacket;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.service.ServiceManager;
import de.hydracloud.base.service.port.PortHandler;
import de.hydracloud.network.NetworkType;
import de.hydracloud.network.packet.Packet;
import io.netty.channel.ChannelHandlerContext;
import lombok.Getter;
import org.apache.commons.io.FileUtils;
import org.jetbrains.annotations.NotNull;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.util.List;
import java.util.Objects;
import java.util.concurrent.CopyOnWriteArrayList;
import java.util.jar.JarInputStream;

public final class SimpleServiceManager implements ServiceManager {

    @Getter
    private final Path wrapperPath;
    @Getter
    private final Path pluginPath;
    private List<CloudService> allCachedServices;
    @Getter
    private String wrapperMainClass;

    public SimpleServiceManager() {
        this.allCachedServices = new CopyOnWriteArrayList<>();
        var storageDirectory = new File("storage/jars");
        this.wrapperPath = new File(storageDirectory, "wrapper.jar").toPath().toAbsolutePath();
        this.pluginPath = new File(storageDirectory, "plugin.jar").toPath();

        try {
            // Ensure the storage directory exists
            if (!storageDirectory.exists() && !storageDirectory.mkdirs()) {
                throw new IOException("Failed to create storage directory.");
            }

            // Copy wrapper and plugin jar
            copyResourceToFile("wrapper.jar", this.wrapperPath);
            copyResourceToFile("plugin.jar", this.pluginPath);

            // Retrieve the main class from the wrapper jar
            try (var jarInputStream = new JarInputStream(Files.newInputStream(this.wrapperPath))) {
                this.wrapperMainClass = jarInputStream.getManifest().getMainAttributes().getValue("Main-Class");
            }
        } catch (IOException e) {
            e.printStackTrace();
        }

        var packetHandler = Base.getInstance().getPacketHandler();
        packetHandler.registerPacketListener(ServiceCopyRequestPacket.class, this::handleServiceCopyRequest);
        packetHandler.registerPacketListener(ServiceUpdatePacket.class, this::handleServiceUpdate);
        packetHandler.registerPacketListener(ServiceRequestShutdownPacket.class, this::handleServiceRequestShutdown);
        packetHandler.registerPacketListener(ServiceStartPacket.class, this::handleServiceStart);
        packetHandler.registerPacketListener(ResponsePacket.class, this::handleResponse);
    }

    private void copyResourceToFile(String resourceName, Path destination) throws IOException {
        try (var inputStream = Objects.requireNonNull(this.getClass().getClassLoader().getResourceAsStream(resourceName))) {
            Files.copy(inputStream, destination, StandardCopyOption.REPLACE_EXISTING);
        }
    }

    private void handleServiceCopyRequest(ChannelHandlerContext ctx, ServiceCopyRequestPacket packet) {
        CloudAPI.getInstance().getServiceManager().getService(packet.getService()).ifPresent(cloudService -> {
            var template = new File("templates/" + cloudService.getGroup().getTemplate());
            var localService = (LocalService) cloudService;
            try {
                FileUtils.copyDirectory(localService.getWorkingDirectory(), template);
            } catch (IOException e) {
                e.printStackTrace();
            }
        });
    }

    private void handleServiceUpdate(ChannelHandlerContext ctx, ServiceUpdatePacket packet) {
        this.getService(packet.getService()).ifPresent(service -> {
            service.setState(packet.getState());
            service.setMaxPlayers(packet.getMaxPlayers());
            service.setMotd(packet.getMotd());
            service.setExtraData(packet.getExtraData());
            Base.getInstance().getEventHandler().call(new CloudServiceUpdateEvent(service));
        });
    }

    private void handleServiceRequestShutdown(ChannelHandlerContext ctx, ServiceRequestShutdownPacket packet) {
        var service = Base.getInstance().getServiceManager().getServiceByNameOrNull(packet.getService());
        if (service != null) {
            service.stop();
        }
    }

    private void handleServiceStart(ChannelHandlerContext ctx, ServiceStartPacket packet) {
        var service = prepareService(packet.getServiceGroup());
        assert service != null;
        if (CloudAPI.getInstance().getServiceManager().getService(service).isPresent()) {
            if (CloudAPI.getInstance().getServiceManager().getService(service).get().getState().equalsIgnoreCase(ServiceState.PREPARED)) {
                this.start(CloudAPI.getInstance().getServiceManager().getService(service).get());
            }
        }
    }

    private void handleResponse(ChannelHandlerContext ctx, ResponsePacket responsePacket) {
        var responseHandler = CloudAPI.getInstance().getPacketHandler().getResponses().get(responsePacket.getUuid());
        if (responseHandler != null) {
            responseHandler.accept(responsePacket.getPacket());
        }
    }

    public String prepareService(String serviceGroup) {
        var groupOpt = CloudAPI.getInstance().getGroupManager().getServiceGroupByName(serviceGroup);
        if (groupOpt.isEmpty()) {
            Base.getInstance().getLogger().log("Group '" + serviceGroup + "' not found.", LogType.ERROR);
            return null;
        }

        var group = groupOpt.get();
        var service = new LocalService(
            group,
            Base.getInstance().getWorkerThread().getPossibleServiceIDByGroup(group),
            PortHandler.getNextPort(group),
            Base.getInstance().getNode().getHostName()
        );

        Base.getInstance().getServiceManager().getAllCachedServices().add(service);
        Base.getInstance().getNode().sendPacketToAll(new ServiceAddPacket(service));
        Base.getInstance().getLogger().log(String.format(
            "§7The group '§b%s§7' starts a new instance of '§b%s§7' (§6Prepared§7)",
            group.getName(),
            service.getName()
        ));


        return service.getName();
    }

    @NotNull
    @Override
    public List<CloudService> getAllCachedServices() {
        return this.allCachedServices;
    }

    @Override
    public void setAllCachedServices(@NotNull List<CloudService> services) {
        this.allCachedServices = services;
    }

    public CloudService start(final CloudService service) {
        startService(service);
        Base.getInstance().getLogger().log(String.format(
            "§7The service '§b%s§7' has been selected and will now be started.",
            service.getName()
        ));

        return service;
    }

    public void startService(@NotNull CloudService service) {
        ((LocalService) service).start();
    }

    public void sendPacketToService(@NotNull CloudService service, @NotNull Packet packet) {
        Base.getInstance().getNode().getClients().stream()
            .filter(client -> client.name().equals(service.getName()))
            .findFirst()
            .ifPresent(client -> client.sendPacket(packet));
    }

    @Override
    public void shutdownService(@NotNull CloudService service) {
        service.stop();
    }

    @Override
    public void updateService(@NotNull CloudService service) {
        var packet = new ServiceUpdatePacket(service);
        Base.getInstance().getNode().sendPacketToType(new QueryPacket(packet, QueryPacket.QueryState.SECOND_RESPONSE), NetworkType.NODE);
        Base.getInstance().getNode().sendPacketToType(packet, NetworkType.WRAPPER);
    }
}
