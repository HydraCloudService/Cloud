package de.hydracloud.api.event.service;

import de.hydracloud.api.service.CloudService;
import org.jetbrains.annotations.NotNull;

public final class CloudServiceUpdateEvent extends DefaultServiceEvent {

    public CloudServiceUpdateEvent(final @NotNull CloudService service) {
        super(service);
    }

}
