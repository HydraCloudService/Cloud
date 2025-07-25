package de.hydracloud.api.groups.impl;

import de.hydracloud.api.version.GameServerVersion;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.groups.ServiceGroup;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;
import org.jetbrains.annotations.NotNull;

import java.util.function.Consumer;

@Getter
@Setter
@AllArgsConstructor
public class SimpleServiceGroup implements ServiceGroup {

    private String name, template, node, motd;
    private int maxMemory, defaultMaxPlayers, minOnlineService, maxOnlineService;
    private double startNewPercentage;
    private boolean isStatic, fallbackGroup, maintenance, autoUpdating;
    private GameServerVersion gameServerVersion;

    @Override
    public void edit(final @NotNull Consumer<ServiceGroup> serviceGroupConsumer) {
        serviceGroupConsumer.accept(this);
        this.update();
    }

    @Override
    public void update() {
        CloudAPI.getInstance().getGroupManager().updateServiceGroup(this);
    }

}
