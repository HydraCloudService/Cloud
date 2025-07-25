package de.hydracloud.base.player;

import de.hydracloud.base.Base;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerUpdatePacket;
import de.hydracloud.api.event.player.CloudPlayerUpdateEvent;
import de.hydracloud.api.network.packet.player.CloudPlayerMessagePacket;
import de.hydracloud.api.player.CloudPlayer;
import de.hydracloud.api.player.impl.AbstractPlayerManager;
import de.hydracloud.network.NetworkType;
import org.jetbrains.annotations.NotNull;

import java.util.List;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

public final class SimplePlayerManager extends AbstractPlayerManager {

    public SimplePlayerManager() {
        super.players = new ConcurrentHashMap<>();
    }

    @Override
    public @NotNull List<CloudPlayer> getAllServicePlayers() {
        return this.getPlayers();
    }

    @Override
    public void registerCloudPlayer(final @NotNull CloudPlayer cloudPlayer) {
        this.getAllServicePlayers().add(cloudPlayer);
    }

    @Override
    public void unregisterCloudPlayer(final @NotNull UUID uuid) {
        this.players.remove(uuid);
    }

    @Override
    public void sendCloudPlayerMessage(@NotNull CloudPlayer cloudPlayer, @NotNull String message) {
        cloudPlayer.getProxyServer().sendPacket(new CloudPlayerMessagePacket(cloudPlayer.getUniqueId(), message));
    }

    @Override
    public void updateCloudPlayer(@NotNull CloudPlayer cloudPlayer) {
        this.updateCloudPlayer(cloudPlayer, CloudPlayerUpdateEvent.UpdateReason.UNKNOWN);
    }

    @Override
    public void updateCloudPlayer(@NotNull CloudPlayer cloudPlayer, @NotNull CloudPlayerUpdateEvent.UpdateReason updateReason) {
        final var packet = new CloudPlayerUpdatePacket(cloudPlayer, updateReason);

        // Update all other nodes and this connected services
        Base.getInstance().getNode().sendPacketToType(new QueryPacket(packet, QueryPacket.QueryState.SECOND_RESPONSE), NetworkType.NODE);

        // Update own service caches
        Base.getInstance().getNode().sendPacketToType(packet, NetworkType.WRAPPER);

        // Call event
        Base.getInstance().getEventHandler().call(new CloudPlayerUpdateEvent(cloudPlayer, updateReason));
    }
}

