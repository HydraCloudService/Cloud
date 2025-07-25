package de.hydracloud.api.event.service;

import de.hydracloud.api.service.CloudService;
import org.jetbrains.annotations.NotNull;

public final class CloudServiceRegisterEvent extends DefaultServiceEvent {

    public CloudServiceRegisterEvent(final @NotNull CloudService service) {
        super(service);
    }

}
