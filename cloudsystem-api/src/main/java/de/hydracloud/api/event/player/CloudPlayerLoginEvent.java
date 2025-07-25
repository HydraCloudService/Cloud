package de.hydracloud.api.event.player;

import de.hydracloud.api.player.CloudPlayer;
import org.jetbrains.annotations.NotNull;

public final class CloudPlayerLoginEvent extends DefaultPlayerEvent {

    public CloudPlayerLoginEvent(final @NotNull CloudPlayer cloudPlayer) {
        super(cloudPlayer);
    }

}
