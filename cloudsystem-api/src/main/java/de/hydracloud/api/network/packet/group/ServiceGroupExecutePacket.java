package de.hydracloud.api.network.packet.group;

import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.NetworkBuf;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@Getter
@NoArgsConstructor
public class ServiceGroupExecutePacket implements Packet {

    private ServiceGroup group;
    private Executor executorType;

    public enum Executor {
        REMOVE, CREATE
    }

    public ServiceGroupExecutePacket(ServiceGroup group, Executor executorType) {
        this.group = group;
        this.executorType = executorType;
    }

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        this.group.write(byteBuf);
        byteBuf.writeInt(executorType.ordinal());
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.group = ServiceGroup.read(byteBuf);
        this.executorType = Executor.values()[byteBuf.readInt()];
    }

}
