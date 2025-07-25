package de.hydracloud.modules.api.annotation.processor;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.JsonObject;
import de.hydracloud.modules.api.annotation.Module;

import javax.annotation.processing.AbstractProcessor;
import javax.annotation.processing.ProcessingEnvironment;
import javax.annotation.processing.RoundEnvironment;
import javax.annotation.processing.SupportedAnnotationTypes;
import javax.lang.model.SourceVersion;
import javax.lang.model.element.Element;
import javax.lang.model.element.ElementKind;
import javax.lang.model.element.Name;
import javax.lang.model.element.TypeElement;
import javax.tools.Diagnostic;
import javax.tools.FileObject;
import javax.tools.StandardLocation;
import java.io.IOException;
import java.io.Writer;
import java.util.HashSet;
import java.util.Set;

@SupportedAnnotationTypes("de.hydracloud.modules.api.annotation.Module")
public class ModuleAnnotationProcessor extends AbstractProcessor {

    private final Gson gson = new GsonBuilder()
        .setPrettyPrinting()
        .disableHtmlEscaping()
        .create();

    private ProcessingEnvironment environment;

    @Override
    public synchronized void init(ProcessingEnvironment processingEnv) {
        super.init(processingEnv);
        this.environment = processingEnv;
    }

    @Override
    public boolean process(Set<? extends TypeElement> annotations, RoundEnvironment roundEnv) {
        if (roundEnv.processingOver()) {
            return false;
        }

        Set<String> generatedFiles = new HashSet<>();

        for (Element element : roundEnv.getElementsAnnotatedWith(Module.class)) {
            if (element.getKind() != ElementKind.CLASS) {
                logError("Only classes can be annotated with " + Module.class.getCanonicalName(), element);
                continue;
            }

            TypeElement typeElement = (TypeElement) element;
            Name qualifiedName = typeElement.getQualifiedName();
            Module module = element.getAnnotation(Module.class);

            if (generatedFiles.contains(qualifiedName.toString())) {
                logWarning("Skipping duplicate generation for: " + qualifiedName, element);
                continue;
            }

            JsonObject jsonObject = createModuleJson(module, qualifiedName.toString());

            try {
                writeModuleInfoFile(jsonObject);
                generatedFiles.add(qualifiedName.toString());
            } catch (IOException e) {
                logError("Failed to generate module-info.json file: " + e.getMessage(), element);
                e.printStackTrace();
            }
        }

        return false;
    }

    private JsonObject createModuleJson(Module module, String mainClass) {
        JsonObject jsonObject = new JsonObject();
        jsonObject.addProperty("name", module.name());
        jsonObject.addProperty("author", module.author());
        jsonObject.addProperty("version", module.version());
        jsonObject.addProperty("description", module.description());
        jsonObject.addProperty("main-class", mainClass);
        jsonObject.addProperty("reloadable", module.reloadable());
        return jsonObject;
    }

    private void writeModuleInfoFile(JsonObject jsonObject) throws IOException {
        FileObject fileObject = environment.getFiler()
            .createResource(StandardLocation.CLASS_OUTPUT, "", "module-info.json");

        try (Writer writer = fileObject.openWriter()) {
            gson.toJson(jsonObject, writer);
        }
    }

    private void logError(String message, Element element) {
        environment.getMessager().printMessage(Diagnostic.Kind.ERROR, message, element);
    }

    private void logWarning(String message, Element element) {
        environment.getMessager().printMessage(Diagnostic.Kind.WARNING, message, element);
    }

    @Override
    public SourceVersion getSupportedSourceVersion() {
        return SourceVersion.latestSupported();
    }
}
