package de.hydracloud.api.event.player;

import de.hydracloud.api.player.CloudPlayer;
import org.jetbrains.annotations.NotNull;

public final class CloudPlayerDisconnectEvent extends DefaultPlayerEvent {

    public CloudPlayerDisconnectEvent(final @NotNull CloudPlayer cloudPlayer) {
        super(cloudPlayer);
    }

}
