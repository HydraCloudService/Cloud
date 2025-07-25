package de.hydracloud.api.network.packet.init;

import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.groups.impl.AbstractGroupManager;
import de.hydracloud.api.player.CloudPlayer;
import de.hydracloud.api.player.impl.AbstractPlayerManager;
import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import lombok.AllArgsConstructor;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.List;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

@AllArgsConstructor
@NoArgsConstructor
public class CacheInitPacket implements Packet {

    private List<ServiceGroup> groups;
    private List<CloudService> services;
    private List<CloudPlayer> players;

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf.writeInt(this.groups.size());
        this.groups.forEach(group -> group.write(byteBuf));
        byteBuf.writeInt(this.services.size());
        this.services.forEach(service -> service.write(byteBuf));
        byteBuf.writeInt(this.players.size());
        this.players.forEach(player -> player.write(byteBuf));
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.groups = new ArrayList<>();
        final var groupSize = byteBuf.readInt();
        for (var i = 0; i < groupSize; i++) {
            this.groups.add(ServiceGroup.read(byteBuf));
        }

        ((AbstractGroupManager) CloudAPI.getInstance().getGroupManager()).setAllCachedServiceGroups(this.groups);

        this.services = new ArrayList<>();
        final var serviceSize = byteBuf.readInt();
        for (int i = 0; i < serviceSize; i++) {
            String groupName = byteBuf.readString();
            ServiceGroup group = this.groups.stream()
                    .filter(g -> g.getName().equalsIgnoreCase(groupName))
                    .findFirst()
                    .orElseThrow(() -> new IllegalStateException("Unknown group: " + groupName));

            boolean useV6 = group.getGameServerVersion().isUseV6();

            CloudService service = CloudService.read(byteBuf, group, useV6);
            this.services.add(service);
        }

        CloudAPI.getInstance().getServiceManager().setAllCachedServices(this.services);

        final var players = new ConcurrentHashMap<UUID, CloudPlayer>();
        final var playerSize = byteBuf.readInt();
        for (var i = 0; i < playerSize; i++) {
            final var cloudPlayer = CloudPlayer.read(byteBuf);
            players.put(cloudPlayer.getUniqueId(), cloudPlayer);
        }

        ((AbstractPlayerManager) CloudAPI.getInstance().getPlayerManager()).setPlayers(players);
    }
}
