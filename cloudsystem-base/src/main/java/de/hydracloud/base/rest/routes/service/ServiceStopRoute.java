package de.hydracloud.base.rest.routes.service;

import de.hydracloud.api.CloudAPI;
import spark.Request;
import spark.Response;
import spark.Route;

public class ServiceStopRoute implements Route {

    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"POST".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only POST requests are allowed for this endpoint.";
        }

        String serviceName = request.queryParams("serviceName");
        if (serviceName == null || serviceName.isEmpty()) {
            response.status(400); // 400 = Bad Request
            return "Error: 'serviceName' parameter is required and cannot be empty.";
        }

        var serviceOptional = CloudAPI.getInstance().getServiceManager().getService(serviceName);
        if (serviceOptional.isPresent()) {
            serviceOptional.get().stop();
            return "Success: Service '" + serviceName + "' has been stopped successfully.";
        } else {
            response.status(404); // 404 = Not Found
            return "Error: Service '" + serviceName + "' does not exist.";
        }
    }
}
