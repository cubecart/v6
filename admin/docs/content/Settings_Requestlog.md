# Request Log

Inspect outgoing HTTP requests made by the store to external services. Available to super users only.

## Log Entries

Each entry can be expanded to show full request and response details:

| Field | Description |
| --- | --- |
| Request Time | When the request was made. |
| Request URL | The external URL that was called. |
| Request Headers | HTTP headers sent with the request. |
| Request Body | The data sent (POST body, JSON payload, etc.). |
| Response Code | HTTP status code with description (e.g. 200 OK, 404 Not Found). |
| Response Headers | Headers returned by the remote server. |
| Response Body | The response data received. |
| Error | Any error message, highlighted for 4xx/5xx responses. |

Requests that returned errors or 4xx/5xx status codes are highlighted in red for quick identification.

Use **Clear All** to delete the entire request log.

> [!TIP]
> The request log is essential for debugging payment gateway issues, shipping API failures, and other third-party integrations. Enable request logging in Settings when troubleshooting.
