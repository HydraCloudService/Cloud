package de.hydracloud.wrapper.group;

import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.groups.impl.AbstractGroupManager;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.group.ServiceGroupUpdatePacket;
import de.hydracloud.wrapper.Wrapper;
import org.jetbrains.annotations.NotNull;

public final class WrapperGroupManager extends AbstractGroupManager {

    @Override
    public void updateServiceGroup(@NotNull ServiceGroup serviceGroup) {
        Wrapper.getInstance().getClient().sendPacket(new QueryPacket(new ServiceGroupUpdatePacket(serviceGroup), QueryPacket.QueryState.FIRST_RESPONSE));
    }

}
