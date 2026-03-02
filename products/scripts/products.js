(async function () {
  const grid = document.getElementById('products-grid');
  const feedback = document.getElementById('products-feedback');
  if (!grid || !feedback) return;

  const money = (amount, currency) => new Intl.NumberFormat('en-US', { style: 'currency', currency: (currency || 'usd').toUpperCase() }).format(amount / 100);

  function productCard(item) {
    const article = document.createElement('article');
    article.className = 'card product-card';

    const img = document.createElement('img');
    img.src = item.image || '/assets/images/hero.jpg';
    img.alt = item.name;
    img.loading = 'lazy';

    const title = document.createElement('h2');
    title.textContent = item.name;

    const desc = document.createElement('p');
    desc.textContent = item.description || 'Premium handcrafted leather good.';

    const price = document.createElement('p');
    price.className = 'price';
    price.textContent = money(item.unit_amount, item.currency);

    const button = document.createElement('button');
    button.className = 'btn buy-btn';
    button.type = 'button';
    button.textContent = 'Buy';
    button.addEventListener('click', () => startCheckout(item.price_id, button));

    article.append(img, title, desc, price, button);
    return article;
  }

  async function startCheckout(priceId, button) {
    button.disabled = true;
    button.textContent = 'Redirecting...';
    try {
      const response = await fetch('/api/stripe/create-checkout-session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ price_id: priceId, quantity: 1 })
      });
      const data = await response.json();
      if (!response.ok || !data.url) throw new Error(data.error || 'Checkout session failed.');
      window.location.href = data.url;
    } catch (error) {
      feedback.textContent = error.message;
      button.disabled = false;
      button.textContent = 'Buy';
    }
  }

  try {
    const response = await fetch('/api/stripe/products.php', { headers: { 'Accept': 'application/json' } });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Unable to load products');
    feedback.textContent = '';
    if (!data.products.length) {
      feedback.textContent = 'No products available right now. Please check back soon.';
      return;
    }
    data.products.forEach((item) => grid.appendChild(productCard(item)));
  } catch (error) {
    feedback.textContent = error.message;
  }
})();
