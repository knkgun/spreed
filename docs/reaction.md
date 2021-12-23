# Reaction API

Base endpoint is: `/ocs/v2.php/apps/spreed/api/v1`

## React to a message

* Method: `POST`
* Endpoint: `/chat/{token}/{messageId}`
* Data:

    field | type | Description
    ---|---|---
    `emoji` | string | the reaction emoji

* Response:
    - Status code:
        + `201 Created`
        + `400 Bad Request` In case of any other error
        + `404 Not Found` When the conversation or message to react could not be found for the participant

## Delete a reaction

* Method: `DELETE`
* Endpoint: `/chat/{token}/{messageId}`
* Data:

    field | type | Description
    ---|---|---
    `emoji` | string | the reaction emoji

* Response:
    - Status code:
        + `201 Created`
        + `400 Bad Request` In case of any other error
        + `404 Not Found` When the conversation or message to react or reaction could not be found for the participant
