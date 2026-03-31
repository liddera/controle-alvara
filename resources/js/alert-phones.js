export default (initialRecipients = []) => ({
    recipients: Array.isArray(initialRecipients)
        ? initialRecipients
              .map((phone) => (phone || "").toString().replace(/\D+/g, ""))
              .map((phone) => (phone.startsWith("00") ? phone.slice(2) : phone))
              .filter((phone, index, array) => phone && array.indexOf(phone) === index)
        : [],
    newPhone: "",

    addRecipient() {
        const normalized = (this.newPhone || "").toString().replace(/\D+/g, "");
        const withoutInternationalPrefix = normalized.startsWith("00")
            ? normalized.slice(2)
            : normalized;

        if (!withoutInternationalPrefix) {
            return;
        }

        if (withoutInternationalPrefix.length < 8 || withoutInternationalPrefix.length > 15) {
            return;
        }

        if (this.recipients.includes(withoutInternationalPrefix)) {
            this.newPhone = "";
            return;
        }

        this.recipients.push(withoutInternationalPrefix);
        this.newPhone = "";
    },

    removeRecipient(phone) {
        this.recipients = this.recipients.filter((item) => item !== phone);
    },
});

