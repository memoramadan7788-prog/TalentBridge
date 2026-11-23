import json
import urllib.request

req = urllib.request.Request(
    "http://127.0.0.1:11434/api/generate",
    data=json.dumps({"model": "llama3.2:latest", "prompt": "Hi, reply in 2 words", "stream": False}).encode("utf-8"),
    headers={"Content-Type": "application/json"}
)

try:
    print("Sending request to Ollama...")
    with urllib.request.urlopen(req, timeout=90) as response:
        result = json.loads(response.read().decode())
        print("Success! Ollama response:", result.get("response"))
except Exception as e:
    print("Error:", e)
