package de.hydracloud.base.rest;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.logger.LogType;
import de.hydracloud.base.rest.routes.DefaultRoute;
import de.hydracloud.base.rest.routes.group.GroupListRoute;
import de.hydracloud.base.rest.routes.handler.CustomExceptionHandler;
import de.hydracloud.base.rest.routes.player.PlayerKickRoute;
import de.hydracloud.base.rest.routes.player.PlayerListRoute;
import de.hydracloud.base.rest.routes.player.PlayerTransferRoute;
import de.hydracloud.base.rest.routes.service.ServiceCreateRoute;
import de.hydracloud.base.rest.routes.service.ServiceListRoute;
import de.hydracloud.base.rest.routes.service.ServiceStartRoute;
import de.hydracloud.base.rest.routes.service.ServiceStopRoute;
import spark.Spark;

import java.io.IOException;
import java.net.ServerSocket;

public class RestBootstrap {
    private static String PASSWORD = null;

    public RestBootstrap(String restPassword) {
        PASSWORD = restPassword;

        if (isPortAvailable()) {
            Spark.port(8080);
        } else {
            throw new RuntimeException("Port is not available");
        }
        Spark.before((req, res) -> {
            String password = req.headers("X-Password");
            if (password == null || !password.equals(PASSWORD)) {
                Spark.halt(401, "Unauthorized");
            }
        });

        registerRoutes();

        Spark.exception(Exception.class, new CustomExceptionHandler());

        CloudAPI.getInstance().getLogger().log("§aRestAPI loaded successfully.", LogType.INFO);
    }

    private boolean isPortAvailable() {
        try (var serverSocket = new ServerSocket(8080)) {
            return true;
        } catch (IOException e) {
            return false;
        }
    }

    protected void registerRoutes() {
        Spark.get("/", new DefaultRoute());

        //Service
        Spark.get("/service/create/", new ServiceCreateRoute());
        Spark.get("/service/start/", new ServiceStartRoute());
        Spark.get("/service/stop/", new ServiceStopRoute());
        Spark.get("/service/list/", new ServiceListRoute());

        //Player
        Spark.get("/player/kick/", new PlayerKickRoute());
        Spark.get("/player/list/", new PlayerListRoute());
        Spark.get("/player/transfer/", new PlayerTransferRoute());

        //Group
        Spark.get("/group/list/", new GroupListRoute());
    }
}
