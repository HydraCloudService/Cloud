package de.hydracloud.api.event.player;

import de.hydracloud.api.player.CloudPlayer;
import de.hydracloud.api.event.CloudEvent;
import lombok.Getter;
import org.jetbrains.annotations.NotNull;

@Getter
public abstract class DefaultPlayerEvent implements CloudEvent {

    private final CloudPlayer player;

    public DefaultPlayerEvent(final @NotNull CloudPlayer player) {
        this.player = player;
    }

}
