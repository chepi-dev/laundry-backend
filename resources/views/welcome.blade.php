<!DOCTYPE html>
<html>
<head>
    <title>Test API</title>
</head>
<body>
    <h1>Data Orders</h1>
    <pre id="result"></pre>

    <script>
        fetch("http://127.0.0.1:8000/api/orders", {
            method: "GET",
            headers: {
                "Authorization": "Bearer 81|vYgvZNGWVFtrqyHehZVe9U3bw6ptBSkbHOJUZgnGd6369e0c",
                "Content-Type": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("result").innerText = JSON.stringify(data, null, 2);
        })
        .catch(error => console.error(error));
    </script>
</body>
</html>