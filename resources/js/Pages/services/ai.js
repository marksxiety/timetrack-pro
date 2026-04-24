import { getCsrfToken } from '../utils/helpers/csrf.js';

/**
 * Read an SSE (Server-Sent Events) stream and invoke a callback for each chunk.
 * @param {Response} response - fetch Response with a streaming body
 * @param {(content: string) => void} onChunk - callback invoked per parsed chunk
 * @returns {Promise<void>}
 */
async function readSSEStream(response, onChunk) {
    const reader = response.body.getReader();
    const decoder = new TextDecoder("utf-8");
    let buffer = "";

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });

        const parts = buffer.split("\n\n");
        buffer = parts.pop();

        for (const part of parts) {
            const line = part.trim();
            if (!line.startsWith("data: ")) continue;

            const payload = line.slice(6);
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

/**
 * @typedef {Object} AIResult
 * @property {boolean} success
 * @property {string} [data]
 * @property {number} [status]
 */

/**
 * Send report data to the AI analysis endpoint and stream the response.
 * @param {string|Object} jsonData - JSON string or object to analyze
 * @param {(content: string) => void} onChunk - streaming callback
 * @returns {Promise<AIResult>}
 */
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

/**
 * Send a reason string to the AI enhancement endpoint and stream the improved version.
 * @param {string} reason - the overtime reason to enhance
 * @param {(fullText: string) => void} onChunk - streaming callback with accumulated text
 * @returns {Promise<AIResult>}
 */
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
