package de.hydracloud.base;

import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.network.packet.service.ServiceAddPacket;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.base.service.LocalService;
import de.hydracloud.base.service.SimpleServiceManager;
import de.hydracloud.base.service.port.PortHandler;

import java.io.IOException;
import java.io.InputStream;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.CompletableFuture;
import java.util.function.Consumer;
import java.util.logging.Level;
import java.util.logging.Logger;

public final class WorkerThread extends Thread {

    private static final int MAX_BOOTABLE_SERVICES = 1;

    private final Base base;
    private final StringBuilder stringBuilder = new StringBuilder();
    private final byte[] buffer = new byte[2048];
    private final List<Runnable> tasks = new ArrayList<>();

    private static final Logger LOGGER = Logger.getLogger(WorkerThread.class.getName());

    WorkerThread(Base base) {
        super("Cloud-Worker-Thread");
        this.base = base;
    }

    @Override
    public void run() {
        while (this.base.isRunning()) {
            checkForQueue();

            this.base.getServiceManager().getAllCachedServices().stream()
                .filter(service -> service instanceof LocalService localService && localService.getProcess() != null)
                .map(service -> (LocalService) service)
                .forEach(localService -> {
                    var process = localService.getProcess();
                    if (process.isAlive()) {
                        readStream(process.getErrorStream(),
                            s -> this.base.getLogger().log("[" + localService.getName() + "] " + s, LogType.SCREEN));
                        if (localService.isScreen()) {
                            readStream(process.getInputStream(),
                                s -> this.base.getLogger().log("[" + localService.getName() + "] " + s, LogType.SCREEN));
                        }
                    }
                });

            tasks.forEach(Runnable::run);
            tasks.clear();

            try {
                Thread.sleep(200L);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                LOGGER.log(Level.SEVERE, "Worker thread interrupted", e);
            }
        }
    }

    private void checkForQueue() {
        addServicesToQueueIfNeededAsync();

        if (minBootableServiceExists()) return;

        var localPreparedServices = this.base.getServiceManager().getAllServicesByState(ServiceState.PREPARED).stream()
            .filter(service -> service.getGroup().getNode().equalsIgnoreCase(this.base.getNode().getName()))
            .toList();

        if (!localPreparedServices.isEmpty()) {
            var serviceToStart = localPreparedServices.get(0);
            ((SimpleServiceManager) this.base.getServiceManager()).start(serviceToStart);
        }
    }

    private void addServicesToQueueIfNeededAsync() {
        CompletableFuture.runAsync(() -> {
            var groupManager = this.base.getGroupManager();
            var serviceManager = this.base.getServiceManager();
            var currentNodeName = this.base.getNode().getName();

            groupManager.getAllCachedServiceGroups().stream()
                .filter(group -> group.getNode().equalsIgnoreCase(currentNodeName))
                .filter(group -> getAmountOfGroupServices(group) < group.getMinOnlineService())
                .forEach(group -> {
                    try {
                        var serviceId = getPossibleServiceIDByGroup(group);
                        var port = PortHandler.getNextPort(group);
                        var host = this.base.getNode().getHostName();

                        LocalService newService;
                        if (!group.getGameServerVersion().isUseV6()) {
                            newService = new LocalService(group, serviceId, port, host);
                        } else {
                            var portV6 = PortHandler.getNextPort(group);
                            newService = new LocalService(group, serviceId, port, portV6, host);
                        }

                        serviceManager.getAllCachedServices().add(newService);
                        this.base.getNode().sendPacketToAll(new ServiceAddPacket(newService));

                        this.base.getLogger().log(String.format(
                            "§7Queued new instance for group §b%s§7 → §b%s§7 (§6Prepared§7)",
                            group.getName(), newService.getName()
                        ));
                    } catch (Exception e) {
                        this.base.getLogger().log(String.format(
                            "§cFailed to queue service for group §b%s§c: %s",
                            group.getName(), e.getMessage()
                        ));
                        e.printStackTrace();
                    }
                });
        });
    }


    private boolean minBootableServiceExists() {
        return getAmountOfBootableServices() >= MAX_BOOTABLE_SERVICES;
    }

    private int getAmountOfBootableServices() {
        return this.base.getServiceManager().getAllServicesByState(ServiceState.STARTING).size();
    }

    private int getAmountOfGroupServices(ServiceGroup serviceGroup) {
        return (int) this.base.getServiceManager().getAllCachedServices().stream()
            .filter(service -> service.getGroup().equals(serviceGroup)).count();
    }

    public int getPossibleServiceIDByGroup(ServiceGroup serviceGroup) {
        int id = 1;
        while (isServiceIdAlreadyExists(serviceGroup, id)) id++;
        return id;
    }

    private boolean isServiceIdAlreadyExists(ServiceGroup serviceGroup, int id) {
        return this.base.getServiceManager().getAllServicesByGroup(serviceGroup).stream()
            .anyMatch(service -> id == service.getServiceId());
    }

    private void readStream(InputStream inputStream, Consumer<String> consumer) {
        try {
            int length;
            while (inputStream.available() > 0 && (length = inputStream.read(buffer)) != -1) {
                stringBuilder.append(new String(buffer, 0, length, StandardCharsets.UTF_8));
                processBuffer(consumer);
            }
        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Error reading from stream", e);
        }
    }

    private void processBuffer(Consumer<String> consumer) {
        var content = stringBuilder.toString();
        if (content.contains("\n")) {
            for (var line : content.split("\n")) consumer.accept(line);
        }
        stringBuilder.setLength(0);
    }

    public void addRunnable(Runnable runnable) {
        tasks.add(runnable);
    }
}
