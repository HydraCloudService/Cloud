package de.hydracloud.base.rest.routes.module;

import de.hydracloud.base.Base;
import de.hydracloud.modules.api.ILoadedModule;
import spark.Request;
import spark.Response;
import spark.Route;

import java.util.List;

public class ModuleListRoute implements Route {

    @Override
    public Object handle(Request request, Response response) throws Exception {
        if (!"GET".equalsIgnoreCase(request.requestMethod())) {
            response.status(405); // 405 = Method Not Allowed
            return "Error: Only GET requests are allowed for this endpoint.";
        }

        if (!Base.getInstance().getModuleProvider().getAllModules().isEmpty()) {
            List<String> moduleNames = Base.getInstance().getModuleProvider()
                .getAllModules()
                .stream()
                .map(ILoadedModule::getName)
                .toList();

            return String.join(", ", moduleNames);
        } else {
            response.status(404); // 404 = Not Found
            return "Error: No modules loaded.";
        }
    }
}
