package de.hydracloud.api.event.group;

import de.hydracloud.api.groups.ServiceGroup;
import org.jetbrains.annotations.NotNull;

public class CloudServiceGroupUpdateEvent extends DefaultServiceGroupEvent {

    public CloudServiceGroupUpdateEvent(final @NotNull ServiceGroup serviceGroup) {
        super(serviceGroup);
    }

}
