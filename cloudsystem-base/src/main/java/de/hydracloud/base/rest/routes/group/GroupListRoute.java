package de.hydracloud.base.rest.routes.group;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.groups.ServiceGroup;
import spark.Request;
import spark.Response;
import spark.Route;

import java.util.List;

public class GroupListRoute implements Route {

    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"GET".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only GET requests are allowed for this endpoint.";
        }

        if (!CloudAPI.getInstance().getGroupManager().getAllCachedServiceGroups().isEmpty()) {
            List<String> groupNames = CloudAPI.getInstance()
                .getGroupManager()
                .getAllCachedServiceGroups()
                .stream()
                .map(ServiceGroup::getName)
                .toList();

            return String.join(", ", groupNames);
        } else {
            response.status(404); // 404 = Not Found
            return "Error: No groups loaded.";
        }
    }
}
