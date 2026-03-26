export default (ownerEmail, initialRecipients = []) => ({
    ownerEmail: (ownerEmail || "").trim().toLowerCase(),
    recipients: Array.isArray(initialRecipients)
        ? initialRecipients
              .map((email) => (email || "").trim().toLowerCase())
              .filter((email, index, array) => email && array.indexOf(email) === index)
        : [],
    newEmail: "",

    addRecipient() {
        const normalized = (this.newEmail || "").trim().toLowerCase();

        if (!normalized) {
            return;
        }

        if (normalized === this.ownerEmail || this.recipients.includes(normalized)) {
            this.newEmail = "";
            return;
        }

        this.recipients.push(normalized);
        this.newEmail = "";
    },

    removeRecipient(email) {
        this.recipients = this.recipients.filter((item) => item !== email);
    },
});
