package de.hydracloud.api;

import de.hydracloud.api.event.EventHandler;
import de.hydracloud.api.event.SimpleEventHandler;
import de.hydracloud.api.extensions.ExtensionManager;
import de.hydracloud.api.extensions.ExtenstionConfiguartion;
import de.hydracloud.api.extensions.signs.CloudSignsConfiguration;
import de.hydracloud.api.groups.GroupManager;
import de.hydracloud.api.json.Document;
import de.hydracloud.api.logger.Logger;
import de.hydracloud.api.network.packet.CustomPacket;
import de.hydracloud.api.network.packet.QueryPacket;
import de.hydracloud.api.network.packet.RedirectPacket;
import de.hydracloud.api.network.packet.ResponsePacket;
import de.hydracloud.api.network.packet.group.ServiceGroupCacheUpdatePacket;
import de.hydracloud.api.network.packet.group.ServiceGroupExecutePacket;
import de.hydracloud.api.network.packet.group.ServiceGroupUpdatePacket;
import de.hydracloud.api.network.packet.init.CacheInitPacket;
import de.hydracloud.api.network.packet.player.*;
import de.hydracloud.api.network.packet.service.*;
import de.hydracloud.api.player.PlayerManager;
import de.hydracloud.api.service.ServiceManager;
import de.hydracloud.network.packet.PacketHandler;
import de.hydracloud.network.packet.auth.NodeHandshakeAuthenticationPacket;
import lombok.Getter;
import org.jetbrains.annotations.NotNull;

import java.io.File;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.HashMap;
import java.util.Map;

@Getter
public abstract class CloudAPI {
    @Getter
    private static String prefix = "§8[§fHydra§bCloud§8] §r";

    @Getter
    protected static CloudAPI instance;
    @Getter
    protected Logger logger;

    private final CloudAPIType cloudAPITypes;
    protected final PacketHandler packetHandler;
    protected final EventHandler eventHandler;

    @Getter public static File cloudSignsFile;
    @Getter public static CloudSignsConfiguration cloudSignsConfiguration;
    @Getter public static ExtenstionConfiguartion extenstionConfiguartion;

    protected CloudAPI(final CloudAPIType cloudAPIType) {
        instance = this;

        this.cloudAPITypes = cloudAPIType;
        this.packetHandler = new PacketHandler(
            NodeHandshakeAuthenticationPacket.class, QueryPacket.class, RedirectPacket.class, CustomPacket.class, ResponsePacket.class, ServiceMemoryRequest.class,
            ServiceGroupCacheUpdatePacket.class, ServiceGroupExecutePacket.class, ServiceGroupUpdatePacket.class,
            CacheInitPacket.class, CloudPlayerDisconnectPacket.class, CloudPlayerKickPacket.class,
            CloudPlayerLoginPacket.class, CloudPlayerMessagePacket.class, CloudPlayerSendServicePacket.class,
            CloudPlayerUpdatePacket.class, ServiceAddPacket.class, ServiceRemovePacket.class,
            ServiceRequestShutdownPacket.class, ServiceUpdatePacket.class, ServiceCopyRequestPacket.class, ServiceStartPacket.class, ServiceBroadcastMessagePacket.class, ServiceDispatchCommandPacket.class);
        this.eventHandler = new SimpleEventHandler();

        var extensionsFile = resolvePath("extensions.json").toFile();
        cloudSignsFile = resolvePath("cloudsigns.json").toFile();
        loadExtensionsConfig(extensionsFile);
        loadSignsConfig(cloudSignsFile);
        (new ExtensionManager()).loadAllExtensions();
    }

    public static Path getCloudPath() {
        try {
            Path jarPath = Paths.get(CloudAPI.class.getProtectionDomain().getCodeSource().getLocation().toURI()).getParent();
            return jarPath.getParent();
        } catch (Exception e) {
            throw new IllegalStateException("Unable to determine main cloud path.", e);
        }
    }

    public static Path resolvePath(String relativePath) {
        return getCloudPath().resolve(relativePath);
    }

    private static void loadExtensionsConfig(@NotNull File file) {
        ExtenstionConfiguartion defaults = new ExtenstionConfiguartion();

        if (file.exists()) {
            ExtenstionConfiguartion loaded = new Document(file).get(ExtenstionConfiguartion.class);

            if (loaded.getExtensions() == null) {
                loaded.setExtensions(new HashMap<>());
            }

            boolean changed = false;

            for (Map.Entry<String, Boolean> entry : defaults.getExtensions().entrySet()) {
                String key = entry.getKey();
                Boolean defaultValue = entry.getValue();

                if (!loaded.getExtensions().containsKey(key)) {
                    loaded.getExtensions().put(key, defaultValue);
                    changed = true;
                }
            }

            extenstionConfiguartion = loaded;

            if (changed) {
                new Document(extenstionConfiguartion).write(file);
            }

        } else {
            extenstionConfiguartion = defaults;
            new Document(extenstionConfiguartion).write(file);
        }
    }


    private static void loadSignsConfig(@NotNull File file) {
        if (file.exists()) {
            cloudSignsConfiguration = new Document(file).get(CloudSignsConfiguration.class);
            return;
        }
        new Document(cloudSignsConfiguration = new CloudSignsConfiguration()).write(file);
    }

    /**
     * @return the group manager
     */
    public abstract @NotNull GroupManager getGroupManager();

    /**
     * @return the service manager
     */
    public abstract @NotNull ServiceManager getServiceManager();

    /**
     * @return the player manager
     */
    public abstract @NotNull PlayerManager getPlayerManager();
}
