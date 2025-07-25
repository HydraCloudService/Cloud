package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.commands;

import cn.nukkit.Player;
import cn.nukkit.command.Command;
import cn.nukkit.command.CommandSender;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.CloudSignsExtension;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider.CloudSignsProvider;

public class CloudSignsCommand extends Command {

    public CloudSignsCommand() {
        super("cloudsigns", "Cloud signs command", "/cs <add | remove | info | reload>", new String[]{"cs", "signs"});
        this.setPermission("cloud.signs");
    }

    @Override
    public boolean execute(CommandSender commandSender, String s, String[] strings) {
        if (!(commandSender instanceof Player)) return false;
        if (!this.testPermission(commandSender)) return false;

        if (strings.length == 0) {
            commandSender.sendMessage("Usage: /cs add <group>");
            commandSender.sendMessage("Usage: /cs remove");
            commandSender.sendMessage("Usage: /cs info");
            return false;
        }

        if (strings.length == 1) {
            if (strings[0].equalsIgnoreCase("reload")) {
                CloudSignsExtension.getInstance().getConfigProvider().reloadConfig();
                CloudSignsProvider.loadCloudSigns();

                commandSender.sendMessage("§aReloaded cloud signs.");
            }

            if (strings[0].equalsIgnoreCase("remove")) {
                CloudSignsProvider.unregisterSigns.add(commandSender.getName());
                commandSender.sendMessage("§aPlease destroy the sign that is to be removed.");
            }

            if (strings[0].equalsIgnoreCase("info")) {
                CloudSignsProvider.infoSign.add(commandSender.getName());
                commandSender.sendMessage("§aPlease destroy the sign for which you want information.");
            }
        }

        if (strings[0].equalsIgnoreCase("add")) {
            String group = strings[1];
            if (!CloudAPI.getInstance().getGroupManager().isServiceGroupExists(group)) {
                commandSender.sendMessage("§cThis group does not exists.");
                return false;
            }

            CloudSignsProvider.registerSigns.put(commandSender.getName(), group);
            commandSender.sendMessage("§aPlease destroy the sign that you want to register as a CloudSign.");
        }

        return false;
    }
}
