import ollama

# 1. فحص الموديلات المثبتة على جهازك
print("--- Ollama Models List ---")
response = ollama.list()
for model in response.models:
    print(f"Model: {model.model}, Size: {model.size / (1024**3):.2f} GB")

print("\n--- Testing Generation with Llama 3.2 ---")
# 2. تجربة التوليد مع Streaming مثل الكود الخاص بك
res = ollama.generate(
    model="llama3.2",
    prompt="Explain in 2 sentences why ATS systems are important for HR recruitment.",
    stream=True
)

for chunk in res:
    print(chunk["response"], end="", flush=True)

print("\n")
