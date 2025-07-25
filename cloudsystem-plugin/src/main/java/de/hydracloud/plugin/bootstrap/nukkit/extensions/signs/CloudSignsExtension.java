package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs;

import cn.nukkit.Server;
import de.hydracloud.plugin.bootstrap.nukkit.NukkitBootstrap;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.commands.CloudSignsCommand;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.events.BlockBreakListener;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.events.PlayerInteractListener;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider.CloudSignsProvider;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider.ConfigProvider;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.tasks.RefreshSignsTask;
import lombok.Getter;

import java.util.ArrayList;

public class CloudSignsExtension {

    @Getter private static CloudSignsExtension instance;
    @Getter private ConfigProvider configProvider;
    @Getter private CloudSignsProvider cloudSignsProvider;

    @Getter public static ArrayList<String> cooldown = new ArrayList<>();
    @Getter NukkitBootstrap plugin;

    @Getter private static String prefix = "§8[§cCloudSigns§8]§r ";

    public void load(NukkitBootstrap plugin) {
        instance = this;
        configProvider = new ConfigProvider();
        cloudSignsProvider = new CloudSignsProvider();

        this.plugin = plugin;

        registerEvents();
        registerCommands();

        Server.getInstance().getScheduler().scheduleRepeatingTask(new RefreshSignsTask(), 20, true);
    }

    public void registerEvents() {
        Server.getInstance().getPluginManager().registerEvents(new BlockBreakListener(), plugin);
        Server.getInstance().getPluginManager().registerEvents(new PlayerInteractListener(), plugin);
    }

    public void registerCommands() {
        Server.getInstance().getCommandMap().register("cloudsigns", new CloudSignsCommand());
    }
}
