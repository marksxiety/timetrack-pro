function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : document.querySelector('meta[name="csrf-token"]')?.content;
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

        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            const chunk = decoder.decode(value);
            if (chunk && typeof onChunk === "function") {
                onChunk(chunk);
            }
        }

        return { success: true };
    } catch (error) {
        console.error("AI Analysis Error:", error);
        return {
            success: false,
            data: error.message || "Unknown error",
        };
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
            } catch { /* use default message */ }
            return { success: false, data: message, status: response.status };
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");
        let fullText = "";

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            const chunk = decoder.decode(value);
            fullText += chunk;
            if (chunk && typeof onChunk === "function") {
                onChunk(fullText);
            }
        }

        return { success: true, data: fullText.trim() };
    } catch (error) {
        console.error("AI Enhancement Error:", error);
        return { success: false, data: error.message };
    }
}
