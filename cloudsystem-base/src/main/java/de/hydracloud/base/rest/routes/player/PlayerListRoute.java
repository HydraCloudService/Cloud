package de.hydracloud.base.rest.routes.player;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.player.CloudPlayer;
import spark.Request;
import spark.Response;
import spark.Route;

import java.util.List;

public class PlayerListRoute implements Route {

    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"GET".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only GET requests are allowed for this endpoint.";
        }

        if (!CloudAPI.getInstance().getPlayerManager().getPlayers().isEmpty()) {
            List<String> playerNames = CloudAPI.getInstance()
                .getPlayerManager()
                .getPlayers()
                .stream()
                .map(CloudPlayer::getUsername)
                .toList();

            return String.join(", ", playerNames);
        } else {
            response.status(404); // 404 = Not Found
            return "Error: No players online.";
        }
    }
}
