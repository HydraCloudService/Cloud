package de.hydracloud.base.rest.routes.service;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.base.Base;
import spark.Request;
import spark.Response;
import spark.Route;

public class ServiceStartRoute implements Route {

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
            if (!serviceOptional.get().getState().equals(ServiceState.PREPARED)) {
                return "Error: Service '" + serviceName + "' is already running.";
            }

            Base.getInstance().getServiceManager().start(serviceOptional.get());
            return "Success: Service '" + serviceOptional.get().getName() + "' has been started successfully.";
        } else {
            response.status(404); // 404 = Not Found
            return "Error: Service '" + serviceName + "' does not exist.";
        }
    }
}
