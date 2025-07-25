package de.hydracloud.wrapper.player;

import de.hydracloud.api.event.player.CloudPlayerDisconnectEvent;
import de.hydracloud.api.event.player.CloudPlayerLoginEvent;
import de.hydracloud.api.event.player.CloudPlayerUpdateEvent;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerDisconnectPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerLoginPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerMessagePacket;
import de.hydracloud.api.network.packet.player.CloudPlayerUpdatePacket;
import de.hydracloud.api.player.CloudPlayer;
import de.hydracloud.api.player.impl.AbstractPlayerManager;
import de.hydracloud.wrapper.Wrapper;
import org.jetbrains.annotations.NotNull;

import java.util.List;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

public final class CloudPlayerManager extends AbstractPlayerManager {

    public CloudPlayerManager() {
        this.players = new ConcurrentHashMap<>();
    }

    @Override
    public @NotNull List<CloudPlayer> getAllServicePlayers() {
        var serviceName = Wrapper.getInstance().thisService().getName();
        return this.players.values().stream()
            .filter(player -> player.getServer().getName().equals(serviceName))
            .toList();
    }

    @Override
    public void registerCloudPlayer(@NotNull CloudPlayer cloudPlayer) {
        this.players.put(cloudPlayer.getUniqueId(), cloudPlayer);
        var eventHandler = Wrapper.getInstance().getEventHandler();
        var client = Wrapper.getInstance().getClient();

        eventHandler.call(new CloudPlayerLoginEvent(cloudPlayer));
        client.sendPacket(new QueryPacket(
            new CloudPlayerLoginPacket(
                cloudPlayer.getAddress(),
                cloudPlayer.getUsername(),
                cloudPlayer.getUniqueId(),
                cloudPlayer.getProxyServer().getName()
            ),
            QueryPacket.QueryState.FIRST_RESPONSE
        ));
    }

    @Override
    public void unregisterCloudPlayer(@NotNull UUID uuid) {
        var removedPlayer = this.players.remove(uuid);
        if (removedPlayer != null) {
            var eventHandler = Wrapper.getInstance().getEventHandler();
            var client = Wrapper.getInstance().getClient();

            eventHandler.call(new CloudPlayerDisconnectEvent(removedPlayer));
            client.sendPacket(new QueryPacket(
                new CloudPlayerDisconnectPacket(uuid),
                QueryPacket.QueryState.FIRST_RESPONSE
            ));
        }
    }

    @Override
    public void sendCloudPlayerMessage(@NotNull CloudPlayer cloudPlayer, @NotNull String message) {
        var packet = new CloudPlayerMessagePacket(cloudPlayer.getUniqueId(), message);
        cloudPlayer.getProxyServer().sendPacket(packet);
    }

    @Override
    public void updateCloudPlayer(@NotNull CloudPlayer cloudPlayer) {
        updateCloudPlayer(cloudPlayer, CloudPlayerUpdateEvent.UpdateReason.UNKNOWN);
    }

    @Override
    public void updateCloudPlayer(@NotNull CloudPlayer cloudPlayer, @NotNull CloudPlayerUpdateEvent.UpdateReason updateReason) {
        var packet = new CloudPlayerUpdatePacket(cloudPlayer, updateReason);
        var eventHandler = Wrapper.getInstance().getEventHandler();
        var client = Wrapper.getInstance().getClient();

        client.sendPacket(new QueryPacket(packet, QueryPacket.QueryState.FIRST_RESPONSE));
        eventHandler.call(new CloudPlayerUpdateEvent(cloudPlayer, updateReason));
    }
}
