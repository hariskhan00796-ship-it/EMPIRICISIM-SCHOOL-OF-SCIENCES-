document.querySelectorAll("[data-admission-form]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const message = form.querySelector("[data-form-message]");
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;

        message.textContent = "Sending your application...";
        message.style.color = "#1a1a1a";
        submitButton.disabled = true;
        submitButton.textContent = "Sending...";

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            });
            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch {
                throw new Error("Your application could not be sent. Please try again later or contact the school.");
            }

            if (!response.ok) {
                throw new Error(result.message || "Your application could not be sent. Please check your details.");
            }

            message.textContent = result.message || "Your application was received. The school will contact you.";
            message.style.color = "#176b36";
            form.reset();
        } catch (error) {
            message.textContent = error instanceof TypeError
                ? "Could not connect. Please try again later or contact the school."
                : error.message || "Could not connect. Please try again.";
            message.style.color = "#a00000";
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
});
