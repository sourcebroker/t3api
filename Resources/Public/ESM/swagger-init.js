let checkExist = setInterval(function() {
  if (window.SwaggerUIBundle) {
    clearInterval(checkExist);
    const element = document.getElementById('t3api-swagger-ui');
    window.ui = SwaggerUIBundle({
      url: element.dataset.specUrl,
      dom_id: '#t3api-swagger-ui',
      // Do not send the specification URL to the online validator of swagger.io (default of Swagger UI)
      validatorUrl: null,
      deepLinking: true,
      presets: [
        SwaggerUIBundle.presets.apis,
        SwaggerUIStandalonePreset
      ],
      plugins: [
        SwaggerUIBundle.plugins.DownloadUrl
      ],
    });
  }
}, 100);
