package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider;

import cn.nukkit.Server;
import cn.nukkit.block.Block;
import cn.nukkit.level.Location;
import cn.nukkit.math.Vector3;
import cn.nukkit.utils.Config;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.CloudSignsExtension;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.utils.CloudSign;
import lombok.Getter;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@Getter
public class CloudSignsProvider {

    @Getter private static final Map<String, CloudSign> signs = new HashMap<>();
    @Getter public static final Map<String, String> registerSigns = new HashMap<>();

    @Getter public static final List<String> servers = new ArrayList<>();
    @Getter public static final List<String> unregisterSigns = new ArrayList<>();
    @Getter public static final List<String> infoSign = new ArrayList<>();

    public CloudSignsProvider() {
        loadCloudSigns();
    }

    public static List<CloudSign> getCloudSignByGroup(String group) {
        return signs.values().stream()
            .filter(cloudSign -> cloudSign.getGroup().equals(group))
            .collect(Collectors.toList());
    }

    public static void loadCloudSigns() {
        signs.clear();
        servers.clear();

        Config config = CloudSignsExtension.getInstance().getConfigProvider().getConfig();
        Map<String, Map<String, Object>> json = config.get("signs", new HashMap<>());
        if (json == null) {
            Server.getInstance().getLogger().error("JSON Config occurred an error!");
            return;
        }

        json.forEach((key, data) -> {
            String[] coords = key.split(":");
            Vector3 vector3 = new Vector3(
                Double.parseDouble(coords[0]),
                Double.parseDouble(coords[1]),
                Double.parseDouble(coords[2])
            );

            String group = data.get("group").toString();

            addSign(new CloudSign(new Location(vector3.getX(), vector3.getY(), vector3.getZ(), Server.getInstance().getDefaultLevel()), group));
        });

        Server.getInstance().getLogger().info("§f" + json.size() + " §ccloud signs loaded.");
    }

    public static void addSign(CloudSign cloudSign) {
        signs.put(cloudSign.getVector3String(), cloudSign);
    }

    public static CloudSign getCloudSignByPosition(String coords) {
        return signs.getOrDefault(coords, null);
    }

    public static void removeSign(CloudSign cloudSign) {
        signs.values().removeIf(sign -> sign.equals(cloudSign));
    }

    public static void registerSign(CloudSign cloudSign) {
        CloudSignsExtension.getInstance().getConfigProvider().registerSign(cloudSign);
        CloudSignsExtension.getInstance().getConfigProvider().reloadConfig();
        addSign(cloudSign);
    }

    public static void unregisterSign(CloudSign cloudSign) {
        if (cloudSign.getFounder() != null) {
            removeServer(cloudSign.getFounder());
        }
        CloudSignsExtension.getInstance().getConfigProvider().unregisterSign(cloudSign);
        removeSign(cloudSign);
    }

    public static boolean isCloudSign(Block block) {
        String blockCoords = block.getX() + ":" + block.getY() + ":" + block.getZ();
        return signs.containsKey(blockCoords);
    }


    public static boolean isServerFree(String serverName) {
        return !servers.contains(serverName);
    }

    public static void addServer(String serverName) {
        servers.add(serverName);
    }

    public static void removeServer(String serverName) {
        servers.remove(serverName);
    }

    public static List<String> getSelectedServers() {
        return new ArrayList<>(servers);
    }
}
