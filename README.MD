####`POST localhost/v1/auth` - Authorization. 
Required JSON body: {
"login": "log2322341",
"password": "asdasd"
} 

####`POST localhost/v1/something` - ADD entity to base
Requires JSON body and token returned when authorized.
{
"field1": "f12132131",
"field2": "123243243"
}

####`GET localhost/v1/something/<id>` - Get entity from base
id - entity id in base. 
Requires authorizing token.

####`PUT localhost/v1/something/<id>` - Update entity info.
Requires JSON body and token returned when authorized.
{
"field1": "f12132131",
"field2": "123243243"
}

####`DELETE localhost/v1/something/<id>` - DELETE entity from base

####`DELETE localhost/v1/something/<id>/safe` - SAFE delete entity from base

####`GET localhost/v1/something/search?field1=<____>&field2=<____>` - Search entity in base by params
