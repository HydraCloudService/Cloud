package de.hydracloud.base.rest.routes.player;

import de.hydracloud.api.CloudAPI;
import spark.Request;
import spark.Response;
import spark.Route;

public class PlayerKickRoute implements Route {

    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"POST".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only POST requests are allowed for this endpoint.";
        }

        String playerName = request.queryParams("playerName");
        if (playerName == null || playerName.isEmpty()) {
            response.status(400); // 400 = Bad Request
            return "Error: 'playerName' parameter is required and cannot be empty.";
        }

        String reason = request.queryParams("reason");
        if (reason == null || reason.isEmpty()) {
            response.status(400); // 400 = Bad Request
            return "Error: 'reason' parameter is required and cannot be empty.";
        }

        var playerOptional = CloudAPI.getInstance().getPlayerManager().getCloudPlayer(playerName);
        if (playerOptional.isPresent()) {
            playerOptional.get().kick(reason);
            return "Success: Player '" + playerName + "' has been kicked successfully.";
        } else {
            response.status(404); // 404 = Not Found
            return "Error: Player '" + playerName + "' is not online.";
        }
    }
}
