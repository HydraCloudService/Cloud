package de.hydracloud.base.group;

import de.hydracloud.api.groups.impl.SimpleServiceGroup;
import de.hydracloud.api.version.GameServerVersion;

public final class DefaultGroup extends SimpleServiceGroup {

    public DefaultGroup(String node, String group, int memory, boolean staticService, GameServerVersion gameServerVersion) {
        super(group, group, node, "A default cloud service", memory, 100, 1,
            -1, 100.0, staticService, false, false, true, gameServerVersion);
    }

}
