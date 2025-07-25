package de.hydracloud.api.event.service;

import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.event.CloudEvent;
import lombok.Getter;
import org.jetbrains.annotations.NotNull;

@Getter
public abstract class DefaultServiceEvent implements CloudEvent {

    private final CloudService service;

    public DefaultServiceEvent(final @NotNull CloudService service) {
        this.service = service;
    }

}
