package de.hydracloud.base.rest.routes.service;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.service.CloudService;
import spark.Request;
import spark.Response;
import spark.Route;

import java.util.List;
import java.util.stream.Collectors;

public class ServiceListRoute implements Route {
    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"GET".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only GET requests are allowed for this endpoint.";
        }

        String groupName = request.queryParams("groupName");
        if (groupName == null || groupName.isEmpty()) {
            response.status(400); // 400 = Bad Request
            return "Error: 'groupName' parameter is required and cannot be empty.";
        }

        var serviceGroupOptional = CloudAPI.getInstance().getGroupManager().getServiceGroup(groupName);
        if (!serviceGroupOptional.isEmpty()) {
            if (!CloudAPI.getInstance().getServiceManager().getAllServicesByGroup(serviceGroupOptional.get(0)).isEmpty()) {
                List<String> serviceNames = CloudAPI.getInstance()
                    .getServiceManager()
                    .getAllServicesByGroup(serviceGroupOptional.get(0))
                    .stream()
                    .map(CloudService::getName)
                    .collect(Collectors.toList());

                return String.join(", ", serviceNames);
            } else {
                response.status(404); // 404 = Not Found
                return "Error: No services online.";
            }
        } else {
            response.status(404); // 404 = Not Found
            return "Error: ServiceGroup '" + serviceGroupOptional + "' does not exist.";
        }
    }
}
