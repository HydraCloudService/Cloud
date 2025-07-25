package de.hydracloud.api.network.packet;

import de.hydracloud.api.json.Document;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.NetworkBuf;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@Getter
@NoArgsConstructor
@AllArgsConstructor
public final class CustomPacket implements Packet {

    private Document document;

    @Override
    public void write(@NotNull NetworkBuf networkByteBuf) {
        networkByteBuf.writeString(this.document.getJsonObject().toString());
    }

    @Override
    public void read(@NotNull NetworkBuf networkByteBuf) {
        this.document = new Document(networkByteBuf.readString());
    }

}
