package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.utils;

import cn.nukkit.Server;
import cn.nukkit.block.Block;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.level.Location;
import cn.nukkit.level.Position;
import cn.nukkit.math.BlockFace;
import cn.nukkit.utils.TextFormat;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider.CloudSignsProvider;
import lombok.Getter;
import lombok.Setter;

import java.util.*;

@Getter
@Setter
public class CloudSign {
    public static final int SEARCH = 1;
    public static final int FULL = 2;
    public static final int JOIN = 3;
    public static final int MAINTENANCE = 4;

    private static final Map<Integer, Integer> FACES = Map.of(
        2, 3,
        3, 2,
        4, 5,
        5, 4
    );

    private final String[] animation = {
        "▁", "▁ ▂", "▁ ▂ ▃", "▁ ▂ ▃ ▅", "▁ ▂ ▃ ▅ ▆", "▁ ▂ ▃ ▅ ▆ ▇", "▁ ▂ ▃ ▅ ▆ ▇ ▉",
        "▁ ▂ ▃ ▅ ▆ ▇", "▁ ▂ ▃ ▅ ▆", "▁ ▂ ▃ ▅", "▁ ▂ ▃", "▁ ▂", "▁", " "
    };

    private final Location position;
    private final String group;
    private int state;
    private String founder;
    private int animationCount;

    public CloudSign(Location position, String group) {
        this.position = position;
        this.group = group;
        this.state = SEARCH;
        this.animationCount = 1;
    }

    public String getVector3String() {
        return String.format("%d:%d:%d", position.getFloorX(), position.getFloorY(), position.getFloorZ());
    }

    public boolean isNearPlayers() {
        return Server.getInstance().getOnlinePlayers().values().stream()
            .anyMatch(player -> player.distanceSquared(position) <= 100);
    }

    public void refreshSign() {
        BlockEntitySign sign = (BlockEntitySign) position.getLevel().getBlockEntity(position);
        if (sign == null) return;

        var service = CloudAPI.getInstance().getServiceManager().getServiceByNameOrNull(founder);
        if (service == null) {
            CloudSignsProvider.removeServer(founder);
            setFounder(null);
            state = SEARCH;
        }

        if (founder != null) {
            if (service != null) {
                if (service.getState().equals(ServiceState.INGAME)) {
                    CloudSignsProvider.removeServer(founder);
                    setFounder(null);
                    state = SEARCH;
                } else if (service.getGroup().isMaintenance()) {
                    state = MAINTENANCE;
                } else if (service.isFull()) {
                    state = FULL;
                } else {
                    state = JOIN;
                }
            }
        }

        if (state == SEARCH) {
            handleSearchState(sign);
        } else if (state == FULL) {
            handleFullState(sign);
        } else if (state == MAINTENANCE) {
            handleMaintenanceState(sign);
        } else if (state == JOIN) {
            handleJoinState(sign);
        }

        if (FACES.containsKey(sign.getBlock().getDamage())) {
            Block block = switch (state) {
                case JOIN -> Block.get(Block.TERRACOTTA, 5);
                case FULL, MAINTENANCE, SEARCH -> Block.get(Block.TERRACOTTA, 14);
                default -> null;
            };

            if (block != null) {
                position.getLevel().setBlock(position.getSide(BlockFace.fromIndex(FACES.get(sign.getBlock().getDamage()))), block);
            }
        }
    }

    private void handleSearchState(BlockEntitySign sign) {
        if (animationCount == 6) {
            var servers = CloudAPI.getInstance().getServiceManager().getAllCachedServices();
            List<String> serverNames = servers.stream()
                .map(CloudService::getName)
                .toList();

            for (String server : serverNames) {
                if (group.equals(server.split("-")[0]) && founder == null) {
                    CloudService service = servers.stream()
                        .filter(s -> s.getName().equals(server))
                        .filter(s -> CloudSignsProvider.isServerFree(s.getName()))
                        .filter(s -> s.getState().equals(ServiceState.ONLINE))
                        .filter(s -> !s.getState().equals(ServiceState.INGAME))
                        .findFirst()
                        .orElse(null);

                    if (service != null) {
                        founder = server;
                        state = JOIN;

                        CloudSignsProvider.addServer(founder);
                        break;
                    }
                }
            }
        }

        sign.setText("", TextFormat.RED + "Server loading", getAnimation(), "");
    }

    private void handleFullState(BlockEntitySign sign) {
        var service = CloudAPI.getInstance().getServiceManager().getServiceByNameOrNull(founder);
        if (service == null) {
            return;
        }

        if (service.isFull()) {
            state = FULL;
        }

        sign.setText(
            TextFormat.YELLOW + founder,
            TextFormat.RED + "FULL",
            TextFormat.RED + String.valueOf(service.getOnlineCount()) + TextFormat.GRAY + " / " + TextFormat.RED + String.valueOf(service.getMaxPlayers()),
            TextFormat.GOLD + "ONLY VIP");
    }

    private void handleMaintenanceState(BlockEntitySign sign) {
        var service = CloudAPI.getInstance().getServiceManager().getServiceByNameOrNull(founder);
        if (service == null) {
            CloudSignsProvider.removeServer(founder);
            setFounder(null);
            state = SEARCH;
            return;
        }

        sign.setText("", TextFormat.DARK_RED + "Maintenance", getAnimation(), "");
    }

    private void handleJoinState(BlockEntitySign sign) {
        var service = CloudAPI.getInstance().getServiceManager().getServiceByNameOrNull(founder);
        if (service == null) {
            return;
        }

        String color = (service.getOnlineCount() >= service.getMaxPlayers() / 2) ? TextFormat.YELLOW.toString() : TextFormat.GREEN.toString();

        sign.setText(
            (founder.length() < 11 ? TextFormat.WHITE + "» " + TextFormat.YELLOW + founder + TextFormat.WHITE + " «" : TextFormat.YELLOW + founder),
            TextFormat.GREEN + "LOBBY",
            color + service.getOnlineCount() + TextFormat.GRAY + " / " + TextFormat.RED + service.getMaxPlayers(),
            TextFormat.GRAY + ""
        );
    }

    public String getAnimation() {
        animationCount++;
        if (animationCount - 1 >= animation.length) {
            animationCount = 1;
        }
        return animation[animationCount - 1] != null ? TextFormat.WHITE + animation[animationCount - 1] : "";
    }
}
