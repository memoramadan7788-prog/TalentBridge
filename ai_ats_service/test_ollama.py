import json
import urllib.request
import urllib.error

OLLAMA_HOST = "http://127.0.0.1:11434"

def test_ollama():
    url = f"{OLLAMA_HOST}/api/generate"
    payload = {
        "model": "llama3.2:latest",
        "prompt": "Respond with only a JSON object: {\"status\": \"ok\", \"message\": \"ATS Ready\"}",
        "format": "json",
        "stream": False
    }
    
    req = urllib.request.Request(
        url,
        data=json.dumps(payload).encode("utf-8"),
        headers={"Content-Type": "application/json"}
    )
    
    try:
        with urllib.request.urlopen(req, timeout=30) as response:
            res_body = response.read().decode("utf-8")
            data = json.loads(res_body)
            print("Response from Ollama:")
            print(data.get("response"))
            return True
    except urllib.error.URLError as e:
        print(f"Error connecting to Ollama: {e}")
        return False

if __name__ == "__main__":
    test_ollama()
