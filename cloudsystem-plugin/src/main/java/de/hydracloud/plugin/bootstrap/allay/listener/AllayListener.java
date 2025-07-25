package de.hydracloud.plugin.bootstrap.allay.listener;

import de.hydracloud.wrapper.Wrapper;
import org.allaymc.api.entity.interfaces.EntityPlayer;
import org.allaymc.api.eventbus.EventHandler;
import org.allaymc.api.eventbus.event.player.PlayerJoinEvent;

public class AllayListener {

    @EventHandler
    public void onJoin(PlayerJoinEvent event) {
        EntityPlayer player = event.getPlayer();
        if (Wrapper.getInstance().getPlayerManager().getCloudPlayer(player.getOriginName()).isEmpty()) {
            player.disconnect("§cPlease join through the proxy");
        }
    }
}