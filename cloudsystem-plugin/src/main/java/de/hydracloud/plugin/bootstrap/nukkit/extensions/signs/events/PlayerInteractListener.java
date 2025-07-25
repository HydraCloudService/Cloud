package de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.events;

import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.block.Block;
import cn.nukkit.blockentity.BlockEntity;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.event.EventHandler;
import cn.nukkit.event.Listener;
import cn.nukkit.event.player.PlayerInteractEvent;
import cn.nukkit.level.Position;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.player.CloudPlayer;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.CloudSignsExtension;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.provider.CloudSignsProvider;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.utils.CloudSign;

public class PlayerInteractListener implements Listener {

    @EventHandler
    public void onInteract(PlayerInteractEvent event) {
        Block block = event.getBlock();
        Player player = event.getPlayer();

        CloudPlayer cloudPlayer = CloudAPI.getInstance().getPlayerManager().getCloudPlayerByNameOrNull(player.getName());
        if (cloudPlayer == null) return;
        if (CloudSignsExtension.cooldown.contains(player.getName())) return;

        BlockEntity sign = Server.getInstance().getDefaultLevel().getBlockEntity(block.asBlockVector3().asVector3());
        if (sign instanceof BlockEntitySign){
            Position position = block.getLocation();
            CloudSign cloudSign = CloudSignsProvider.getCloudSignByPosition(String.format("%d:%d:%d", position.getFloorX(), position.getFloorY(), position.getFloorZ()));
            if (cloudSign != null) {
                if (cloudSign.getState() == CloudSign.MAINTENANCE) {
                    if (player.hasPermission("cloud.join.maintenance") && cloudSign.getFounder() != null) {
                        cloudPlayer.connect(cloudSign.getFounder());
                    }
                } else if (cloudSign.getState() == CloudSign.JOIN) {
                    if (cloudSign.getFounder() != null) {
                        cloudPlayer.connect(cloudSign.getFounder());
                    }
                }
            }
        }
    }
}
