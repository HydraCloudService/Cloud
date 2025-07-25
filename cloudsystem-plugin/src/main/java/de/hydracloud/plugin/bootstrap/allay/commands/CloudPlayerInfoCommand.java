package de.hydracloud.plugin.bootstrap.allay.commands;

import de.hydracloud.api.CloudAPI;
import lombok.extern.slf4j.Slf4j;
import org.allaymc.api.command.SenderType;
import org.allaymc.api.command.SimpleCommand;
import org.allaymc.api.command.tree.CommandTree;
import org.allaymc.api.permission.PermissionGroups;

@Slf4j
public class CloudPlayerInfoCommand extends SimpleCommand {

    public CloudPlayerInfoCommand() {
        super("playerinfo", "Cloud PlayerInfo Command");
        getPermissions().forEach(PermissionGroups.OPERATOR::addPermission);
    }

    @Override
    public void prepareCommandTree(CommandTree tree) {
        tree.getRoot()
                .str("player")
                .exec((context, commandSender) -> {
                    String player = context.getResult(0);
                    if (CloudAPI.getInstance().getPlayerManager().getCloudPlayer(player).isEmpty()) {
                        commandSender.sendText("§cThis player is not online.");
                        return context.fail();
                    } else {
                        final var cloudPlayer = CloudAPI.getInstance().getPlayerManager().getCloudPlayer(player).get();
                        commandSender.sendText("§8━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                        commandSender.sendText("§7§l[ §ePlayer Info §7]");
                        commandSender.sendText("§8━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                        commandSender.sendText("§fName: §e" + cloudPlayer.getUsername());
                        commandSender.sendText("§fServer: §e" + cloudPlayer.getServer().getName());
                        commandSender.sendText("§fProxy: §e" + cloudPlayer.getProxyServer().getName());
                        commandSender.sendText("§fAddress: §e" + cloudPlayer.getAddress());
                        commandSender.sendText("§fUUID: §e" + cloudPlayer.getUniqueId());
                        commandSender.sendText("§8━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                        return context.success();
                    }
                }, SenderType.PLAYER);
    }
}