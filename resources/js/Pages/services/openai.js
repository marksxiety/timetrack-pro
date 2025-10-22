export async function analyzeWithAI(jsonData, onChunk) {
    try {
        const content =
            typeof jsonData === "string"
                ? jsonData
                : JSON.stringify(jsonData, null, 2);

        const response = await fetch("/openai/analyze", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content,
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

export async function enhanceReasonWithAI(reason) {
    try {
        const response = await fetch("/openai/enhance", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content,
            },
            body: JSON.stringify({ reason }),
        });

        const data = await response.json();
        if (!data.success) throw new Error(data.message || "Enhancement failed");

        return { success: true, data: data.enhanced_text };
    } catch (error) {
        console.error("AI Enhancement Error:", error);
        return { success: false, data: error.message };
    }
}
