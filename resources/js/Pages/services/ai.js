// Shared SSE stream reader
async function readSSEStream(response, onChunk) {
    const reader = response.body.getReader();
    const decoder = new TextDecoder("utf-8");
    let buffer = "";

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });

        // SSE messages are separated by double newlines
        const parts = buffer.split("\n\n");
        buffer = parts.pop(); // keep incomplete trailing chunk

        for (const part of parts) {
            const line = part.trim();
            if (!line.startsWith("data: ")) continue;

            const payload = line.slice(6); // strip "data: "
            if (payload === "[DONE]") return;

            try {
                const { content } = JSON.parse(payload);
                if (content && typeof onChunk === "function") {
                    onChunk(content);
                }
            } catch {
                // malformed chunk, skip
            }
        }
    }
}

export async function analyzeWithAI(jsonData, onChunk) {
    try {
        const content =
            typeof jsonData === "string"
                ? jsonData
                : JSON.stringify(jsonData, null, 2);

        const response = await fetch("/ai/analyze", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-XSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify({ content }),
        });

        if (!response.ok) {
            throw new Error(`AI analyze failed (status ${response.status})`);
        }

        await readSSEStream(response, onChunk);
        return { success: true };

    } catch (error) {
        console.error("AI Analysis Error:", error);
        return { success: false, data: error.message || "Unknown error" };
    }
}

export async function enhanceReasonWithAI(reason, onChunk) {
    try {
        const response = await fetch("/ai/enhance", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-XSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify({ reason }),
        });

        if (!response.ok) {
            let message = `AI enhance failed (status ${response.status})`;
            try {
                const body = await response.json();
                message = body.error || message;
            } catch { /* use default */ }
            return { success: false, data: message, status: response.status };
        }

        let fullText = "";
        await readSSEStream(response, (chunk) => {
            fullText += chunk;
            if (typeof onChunk === "function") onChunk(fullText);
        });

        return { success: true, data: fullText.trim() };

    } catch (error) {
        console.error("AI Enhancement Error:", error);
        return { success: false, data: error.message };
    }
}

function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : document.querySelector('meta[name="csrf-token"]')?.content;
}