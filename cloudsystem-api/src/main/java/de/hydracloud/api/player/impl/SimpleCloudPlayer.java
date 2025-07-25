package de.hydracloud.api.player.impl;

import  de.hydracloud.api.service.CloudService;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.event.player.CloudPlayerUpdateEvent;
import de.hydracloud.api.player.CloudPlayer;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.RequiredArgsConstructor;
import lombok.Setter;
import org.jetbrains.annotations.NotNull;

import java.util.UUID;

@Getter
@Setter
@AllArgsConstructor
@RequiredArgsConstructor
public final class SimpleCloudPlayer implements CloudPlayer {

    private final String address;
    private final UUID uniqueId;
    private final String username;
    private final CloudService proxyServer;
    private CloudService server;

    @Override
    public void update() {
        CloudAPI.getInstance().getPlayerManager().updateCloudPlayer(this);
    }

    @Override
    public void update(@NotNull CloudPlayerUpdateEvent.UpdateReason updateReason) {
        CloudAPI.getInstance().getPlayerManager().updateCloudPlayer(this, updateReason);
    }

}


