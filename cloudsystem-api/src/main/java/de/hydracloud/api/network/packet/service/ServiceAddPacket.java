package de.hydracloud.api.network.packet.service;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.NetworkBuf;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@AllArgsConstructor
@Getter
@NoArgsConstructor
public class ServiceAddPacket implements Packet {

    private CloudService service;

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        this.service.write(byteBuf);
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        String groupName = byteBuf.readString();

        var group = CloudAPI.getInstance()
                .getGroupManager()
                .getAllCachedServiceGroups()
                .stream()
                .filter(g -> g.getName().equalsIgnoreCase(groupName))
                .findFirst()
                .orElseThrow(() -> new IllegalStateException("Unknown group: " + groupName));

        boolean useV6 = group.getGameServerVersion().isUseV6();
        this.service = CloudService.read(byteBuf, group, useV6);
    }
}
