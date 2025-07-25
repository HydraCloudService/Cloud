package de.hydracloud.plugin.bootstrap.global;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.network.packet.service.ServiceCopyRequestPacket;
import de.hydracloud.api.network.packet.service.ServiceStartPacket;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.wrapper.Wrapper;
import io.netty.util.internal.StringUtil;
import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class CloudGlobalCommand {

    public static void execute(final @NotNull PlayerMessageObject source, final String[] arguments) {
        if (!source.hasPermission("cloud.network.command")) {
            source.sendMessage("§cYou have no permissions for this command.");
            return;
        }

        final var serviceManager = CloudAPI.getInstance().getServiceManager();
        final var groupManager = CloudAPI.getInstance().getGroupManager();

        if (arguments.length == 2) {
            if (arguments[0].equalsIgnoreCase("start")) {
                groupManager.getServiceGroupByName(arguments[1]).ifPresentOrElse(serviceGroup -> {
                    Wrapper.getInstance().getClient().sendPacket(new ServiceStartPacket(arguments[1]));
                    source.sendMessage(CloudAPI.getPrefix() + "§7You have started a new service with the template §e" + arguments[1] + "§7.");
                }, () -> source.sendMessage(CloudAPI.getPrefix() + "§cThis cloud group does not exists."));
                return;
            }
        }

        if (arguments.length == 2) {
            if (arguments[0].equalsIgnoreCase("shutdown")) {
                serviceManager.getService(arguments[1]).ifPresentOrElse(cloudService -> {
                    cloudService.stop();
                    source.sendMessage(CloudAPI.getPrefix() + "§7You stop the service §8'§b" + cloudService.getName() + "§8'");
                }, () -> groupManager.getServiceGroupByName(arguments[1]).ifPresentOrElse(group -> {
                    List<CloudService> allServicesByGroup = serviceManager.getAllServicesByGroup(group);
                    source.sendMessage(CloudAPI.getPrefix() + "§7The service(s) §b" + String.join(", ",
                        allServicesByGroup.stream().map(CloudService::getName).toList()) + " §7trying to shutdown.");
                    allServicesByGroup.forEach(CloudService::stop);
                }, () -> source.sendMessage(CloudAPI.getPrefix() + "§cThis group or service does not exists.")));
                return;
            }
            if (arguments[0].equalsIgnoreCase("info")) {
                serviceManager.getService(arguments[1]).ifPresentOrElse(cloudService -> {
                    source.sendMessage("§8-> §7All information about the service: §f" + cloudService.getName());
                    source.sendMessage("§8» §7Service state: §b" + cloudService.getState());
                    source.sendMessage("§8» §7Motd: §b" + cloudService.getMotd());
                    source.sendMessage("§8» §7Players: §8(§b" + cloudService.getOnlineCount() + "§8/§b" + cloudService.getMaxPlayers() + "§8)");
                    source.sendMessage("§8» §7Service node: §b" + cloudService.getNode());
                    source.sendMessage("§8» §7Port: §b" + cloudService.getPort());
                }, () -> groupManager.getServiceGroupByName(arguments[1]).ifPresentOrElse(group -> {
                    source.sendMessage("§8-> §7All information about the group: §f" + group.getName());
                    source.sendMessage("§8» §7Memory: §b" + group.getMaxMemory());
                    source.sendMessage("§8» §7Version: §b" + group.getGameServerVersion().getName());
                    source.sendMessage("§8» §7Default max players: §b" + group.getDefaultMaxPlayers());
                    source.sendMessage("§8» §7Min online service: §b" + group.getMinOnlineService());
                    source.sendMessage("§8» §7Max online service: §b" + group.getMaxOnlineService());
                    source.sendMessage("§8» §7Node(s): §b" + group.getNode());
                }, () -> source.sendMessage("§cThis group or service does not exists.")));
                return;
            }

            if (arguments[0].equalsIgnoreCase("copy")) {
                serviceManager.getService(arguments[1]).ifPresentOrElse(cloudService -> {
                    Wrapper.getInstance().getClient().sendPacket(new ServiceCopyRequestPacket(cloudService.getName()));
                    source.sendMessage(CloudAPI.getPrefix() + "§7You copy the service §8'§b" + cloudService.getName() + "§8'");
                }, () -> source.sendMessage(CloudAPI.getPrefix() + "§cThis service does not exists."));
                return;
            }
        }
        if (arguments.length == 1 && arguments[0].equalsIgnoreCase("list")) {
            final var nodeServices = new HashMap<String, List<CloudService>>();
            for (final var allCachedService : serviceManager.getAllCachedServices()) {
                final var current = nodeServices.getOrDefault(allCachedService.getNode(), new ArrayList<>());
                current.add(allCachedService);
                nodeServices.put(allCachedService.getNode(), current);
            }
            nodeServices.keySet().forEach(it -> {
                final var services = nodeServices.get(it).stream().filter(s -> s.getGroup().getGameServerVersion().isProxy()).toList();
                source.sendMessage("§8» §7" + it + "§8: (§7Proxies: §c" + services.size() + " Services §8| §f"
                    + services.stream().mapToInt(CloudService::getOnlineCount).sum() + " Players§8)");
                services.forEach(ser -> source.sendMessage("§8» §f" + ser.getName()
                    + "§8 (§b" + ser.getOnlineCount() + "§8/§b" + ser.getMaxPlayers() + " §8| §b" + ser.getState() + " §8| §b" + ser.getNode() + "§8)"));
                source.sendMessage(StringUtil.EMPTY_STRING);

                final var server = nodeServices.get(it).stream().filter(s -> !s.getGroup().getGameServerVersion().isProxy()).toList();
                source.sendMessage("§8» §7" + it + "§8: (§7Server: §c" + server.size() + " Services §8| §f"
                    + server.stream().mapToInt(CloudService::getOnlineCount).sum() + " Players§8)");
                server.forEach(ser -> source.sendMessage("§8» §f" + ser.getName()
                    + "§8 (§b" + ser.getOnlineCount() + "§8/§b" + ser.getMaxPlayers() + " §8| §b" + ser.getState() + " §8| §b" + ser.getNode() + "§8)"));
                source.sendMessage(StringUtil.EMPTY_STRING);
            });
            return;
        }

        source.sendMessage(CloudAPI.getPrefix() + "§8» §b/cloud list §8- §7List all cloud services of every node.");
        source.sendMessage(CloudAPI.getPrefix() + "§8» §b/cloud start (group) §8- §7Start a new service from a specific group.");
        source.sendMessage(CloudAPI.getPrefix() + "§8» §b/cloud shutdown (service/group) §8- §7Stop a current component.");
        source.sendMessage(CloudAPI.getPrefix() + "§8» §b/cloud info (service/group) §8- §7Information about a component.");
    }
}
