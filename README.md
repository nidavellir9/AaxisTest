# PHP API Sample doc

### Guía de ejecución
Luego de clonarse el repositorio ejecutar el comando
`docker-compose up -d`

Luego desde un cliente al estilo de Postman invocar los siguientes endpoints:

Para obtener un token para poder consumir la API debemos loguearnos a través del endpoint

http://localhost/api/login_check

Mediante un POST pasando

```
{
  "username":"admin",
  "password":"0okmnji9"
}
```

Esto nos retornará un token el cuál deberemos enviar en cada petición a través de header en el campo *Authorization*

#### Para generar un nuevo producto, el endpoint es:

**http://localhost/api/saveproduct**

#### Para obtener todos los productos, el endpoint a llamar es:

**http://localhost/api/products**

#### Y por último, para realizar la actualización de productos, el endpoint en cuestión es:

**http://localhost/api/updateproduct**
