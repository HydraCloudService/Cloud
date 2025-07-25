package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider;

import cn.nukkit.utils.Config;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.utils.CloudSign;
import lombok.Getter;

import java.util.HashMap;
import java.util.Map;

public class ConfigProvider {
    @Getter private Config config;

    public ConfigProvider() {
        if (!CloudAPI.getCloudSignsFile().exists()) {
            var c = new Config(CloudAPI.getCloudSignsFile(), Config.JSON);
            c.set("signs", new HashMap<>());
            c.save();
        }

        config = new Config(CloudAPI.getCloudSignsFile(), Config.JSON);
    }

    public void registerSign(CloudSign sign) {
        Config c = config;

        Map<String, Object> signs = c.get("signs", new HashMap<>());
        signs.put(sign.getVector3String(), Map.of(
            "group", sign.getGroup()
        ));

        c.set("signs", signs);
        c.save();
    }

    public void unregisterSign(CloudSign sign) {
        Config c = config;
        Map<String, Object> signs = c.get("signs", new HashMap<>());

        signs.remove(sign.getVector3String());
        c.set("signs", signs);
        c.save();
    }

    public void reloadConfig() {
        config = new Config(CloudAPI.getCloudSignsFile(), Config.JSON);
    }
}
