package de.hydracloud.plugin.bootstrap.waterdogpe.extensions.hub.command;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.hub.HubExtension;
import dev.waterdog.waterdogpe.ProxyServer;
import dev.waterdog.waterdogpe.command.Command;
import dev.waterdog.waterdogpe.command.CommandSender;
import dev.waterdog.waterdogpe.command.CommandSettings;
import dev.waterdog.waterdogpe.player.ProxiedPlayer;

public class HubCommand extends Command {

    public HubCommand() {
        super("hub", CommandSettings.builder()
                .setAliases("lobby", "l")
                .build());
    }

    @Override
    public boolean onExecute(CommandSender commandSender, String s, String[] strings) {
        if (commandSender instanceof ProxiedPlayer) {
            CloudAPI.getInstance().getPlayerManager().getCloudPlayer(((ProxiedPlayer) commandSender).getUniqueId()).ifPresent(cloudPlayer -> {
                if (!cloudPlayer.getServer().getGroup().isFallbackGroup()) {
                    HubExtension.getInstance().getBootstrap().getFallback((ProxiedPlayer) commandSender)
                            .map(service -> ProxyServer.getInstance().getServerInfo(service.getName()))
                            .ifPresentOrElse(
                                    serverInfo -> ((ProxiedPlayer) commandSender).connect(serverInfo),
                                    () -> commandSender.sendMessage(CloudAPI.getPrefix() + "§cNo fallback could be found.")
                            );
                } else {
                    commandSender.sendMessage(CloudAPI.getPrefix() + "§cYou are already on a lobby server.");
                }
            });
        }
        return true;
    }
}
