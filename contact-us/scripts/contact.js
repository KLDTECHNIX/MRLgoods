(function () {
  const form = document.getElementById('contact-form');
  const feedback = document.getElementById('contact-feedback');
  const startedInput = document.getElementById('form_started');
  if (!form || !feedback || !startedInput) return;

  startedInput.value = String(Math.floor(Date.now() / 1000));

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    feedback.textContent = 'Sending message...';

    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, { method: 'POST', body: formData });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'Unable to send your message.');
      feedback.textContent = data.message || 'Message sent. Thank you!';
      form.reset();
      startedInput.value = String(Math.floor(Date.now() / 1000));
    } catch (error) {
      feedback.textContent = error.message;
    }
  });
})();
