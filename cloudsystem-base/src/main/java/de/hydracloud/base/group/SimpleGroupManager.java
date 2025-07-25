package de.hydracloud.base.group;

import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.groups.impl.AbstractGroupManager;
import de.hydracloud.api.groups.impl.SimpleServiceGroup;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.group.ServiceGroupExecutePacket;
import de.hydracloud.api.network.packet.group.ServiceGroupUpdatePacket;
import de.hydracloud.api.version.GameServerVersion;
import de.hydracloud.base.Base;
import de.hydracloud.database.CloudDatabaseProvider;
import de.hydracloud.network.NetworkType;
import org.jetbrains.annotations.NotNull;

import java.util.Arrays;
import java.util.Collections;
import java.util.List;
import java.util.stream.Collectors;

public final class SimpleGroupManager extends AbstractGroupManager {

    private final CloudDatabaseProvider database;

    public SimpleGroupManager() {
        final var base = Base.getInstance();
        final SimpleGroupManager self = this;

        this.database = base.getDatabaseManager().getProvider();

        super.allCachedServiceGroups = this.database.getAllServiceGroups();

        if (!this.allCachedServiceGroups.isEmpty()) {
            base.getLogger().log("§7Loading following groups: §b"
                    + this.getAllCachedServiceGroups().stream()
                    .map(ServiceGroup::getName)
                    .collect(Collectors.joining("§7, §b")));
        } else {
            base.getWorkerThread().addRunnable(() -> {
                askCreateDefaultGroups(self);
            });
        }
    }

    private void askCreateDefaultGroups(SimpleGroupManager self) {
        Base.getInstance().getLogger().log("0 groups loaded, should default groups be created? (yes/no)");
        Base.getInstance().getConsoleManager().addInput(input -> {
            if (input.equalsIgnoreCase("yes")) {
                askProxyVersion(self);
            } else if (input.equalsIgnoreCase("no")) {
                Base.getInstance().getLogger().log("Default groups were not created.");
            } else {
                Base.getInstance().getLogger().log("Invalid input! Possible answers: 'yes', 'no'");
                askCreateDefaultGroups(self);
            }
        }, Arrays.asList("yes", "no"));
    }

    private void askProxyVersion(SimpleGroupManager self) {
        Base.getInstance().getLogger().log("Which proxy version do you want to use? (waterdog)");
        Base.getInstance().getConsoleManager().addInput(proxyVersion -> {
            if (proxyVersion.equalsIgnoreCase("waterdog")) {
                Base.getInstance().getLogger().log("You chose proxy version: §b" + proxyVersion);
                askServerVersion(self, proxyVersion);
            } else {
                Base.getInstance().getLogger().log("Invalid input! Possible answers: 'waterdog'");
                askProxyVersion(self);
            }
        }, List.of("waterdog"));
    }

    private void askServerVersion(SimpleGroupManager self, String proxyVersion) {
        Base.getInstance().getLogger().log("Which server version do you want to use? (nukkit, allay)");
        Base.getInstance().getConsoleManager().addInput(serverVersion -> {
            if (serverVersion.equalsIgnoreCase("nukkit") || serverVersion.equalsIgnoreCase("allay")) {
                Base.getInstance().getLogger().log("You chose server version: §b" + serverVersion);

                if (proxyVersion.equalsIgnoreCase("waterdog")) {
                    self.addServiceGroup(new SimpleServiceGroup("Proxy", "Proxy",
                            Base.getInstance().getNode().getName(), "A default cloud service", 512,
                            100, 1, -1, 100.0, false, false,
                            false, true, GameServerVersion.WATERDOG));
                }

                if (serverVersion.equalsIgnoreCase("nukkit")) {
                    self.addServiceGroup(new SimpleServiceGroup("Lobby", "Lobby",
                            Base.getInstance().getNode().getName(), "A default cloud service", 1024,
                            100, 1, -1, 100.0, false, true,
                            false, true, GameServerVersion.NUKKIT));
                } else if (serverVersion.equalsIgnoreCase("allay")) {
                    self.addServiceGroup(new SimpleServiceGroup("Lobby", "Lobby",
                            Base.getInstance().getNode().getName(), "A default cloud service", 1024,
                            100, 1, -1, 100.0, false, true,
                            false, true, GameServerVersion.ALLAY));
                }

                Base.getInstance().getLogger().log("§7You created following groups: §bLobby (" + serverVersion.toLowerCase() + ")§7, §bProxy (" + proxyVersion.toLowerCase() + ")");
            } else {
                Base.getInstance().getLogger().log("Invalid input! Possible answers 'nukkit', 'allay'");
                askServerVersion(self, proxyVersion);
            }
        }, Arrays.asList("nukkit", "allay"));
    }

    @Override
    public void addServiceGroup(final @NotNull ServiceGroup serviceGroup) {
        this.database.addGroup(serviceGroup);
        Base.getInstance().getNode().sendPacketToAll(new ServiceGroupExecutePacket(serviceGroup, ServiceGroupExecutePacket.Executor.CREATE));
        super.addServiceGroup(serviceGroup);
    }

    @Override
    public void removeServiceGroup(final @NotNull ServiceGroup serviceGroup) {
        this.database.removeGroup(serviceGroup);
        Base.getInstance().getNode().sendPacketToAll(new ServiceGroupExecutePacket(serviceGroup, ServiceGroupExecutePacket.Executor.REMOVE));
        super.removeServiceGroup(serviceGroup);
    }

    @Override
    public void updateServiceGroup(@NotNull ServiceGroup serviceGroup) {
        final var packet = new ServiceGroupUpdatePacket(serviceGroup);
        // update all other nodes and this service groups
        Base.getInstance().getNode().sendPacketToType(new QueryPacket(packet, QueryPacket.QueryState.SECOND_RESPONSE), NetworkType.NODE);
        // update own service group caches
        Base.getInstance().getNode().sendPacketToType(packet, NetworkType.WRAPPER);
    }

}
