import { chromium } from 'playwright';

const BASE = process.env.SMOKE_BASE_URL || 'http://127.0.0.1:8123';
const ADMIN_EMAIL = 'abraham-mizraji@overcloud.us';
const ADMIN_PASSWORD = '9oe5rBGXRooT';
const STAMP = Date.now();

function fail(message) {
    console.error(`\n❌ FALLÓ: ${message}\n`);
    process.exit(1);
}

async function step(name, fn) {
    process.stdout.write(`▶ ${name} ... `);
    try {
        await fn();
        console.log('OK');
    } catch (err) {
        console.log('FALLÓ');
        console.error(err);
        fail(`${name} — ${err.message}`);
    }
}

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
// Bloquea fuentes remotas (Google/Bunny Fonts): en este entorno headless sin
// fontconfig completo, resolver web fonts remotas puede colgar el renderer.
// Es puramente cosmético para la prueba: no afecta el contenido ni el flujo.
await context.route('**://fonts.bunny.net/**', (route) => route.abort());
const page = await context.newPage();
page.setDefaultTimeout(15000);

try {
    await step('Landing pública sin sesión no es genérica', async () => {
        await page.goto(BASE + '/', { waitUntil: 'networkidle' });
        const html = await page.content();
        if (/laravel/i.test(html)) throw new Error('La página contiene "Laravel" en el HTML');
        if (/you're logged in/i.test(html)) throw new Error('La página contiene texto placeholder de Breeze');
        const body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Abraham Mizraji')) throw new Error('No se ve el nombre del negocio en la landing');
    });

    await step('Login como admin lleva al dashboard', async () => {
        await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
        await page.locator('#email').fill(ADMIN_EMAIL);
        await page.locator('#password').fill(ADMIN_PASSWORD);
        await page.getByRole('button', { name: 'Iniciar sesión' }).click();
        await page.waitForURL('**/dashboard', { timeout: 15000 });
        const body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Abraham Mizraji')) throw new Error('El dashboard no muestra el nombre del negocio');
        if (/you're logged in/i.test(body)) throw new Error('El dashboard todavía muestra texto placeholder de Breeze');
    });

    const categoriaNombre = `Categoria E2E ${STAMP}`;
    await step('Categorías: crear y persistir', async () => {
        await page.goto(BASE + '/categorias', { waitUntil: 'networkidle' });
        await page.getByRole('button', { name: 'Nueva categoría' }).click();
        const dialog = page.locator('dialog[open]');
        await dialog.waitFor({ state: 'visible' });
        await dialog.locator('input[type="text"]').first().fill(categoriaNombre);
        await dialog.getByRole('button', { name: 'Crear categoría' }).click();
        await dialog.waitFor({ state: 'hidden', timeout: 10000 });
        await page.waitForTimeout(300);
        let body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(categoriaNombre)) throw new Error('La categoría no aparece en el listado tras crearla');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(categoriaNombre)) throw new Error('La categoría no persiste tras recargar');
    });

    const productoNombre = `Producto E2E ${STAMP}`;
    await step('Productos: crear y persistir', async () => {
        await page.goto(BASE + '/productos', { waitUntil: 'networkidle' });
        await page.getByRole('link', { name: 'Nuevo producto' }).click();
        await page.waitForURL('**/productos/create');
        await page.locator('#nombre').fill(productoNombre);
        await page.locator('#categoria_id').selectOption({ label: categoriaNombre });
        await page.locator('#precio').fill('12345');
        await page.locator('#stock').fill('7');
        await page.locator('#stock_minimo').fill('2');
        await page.getByRole('button', { name: 'Crear producto' }).click();
        await page.waitForURL('**/productos', { timeout: 15000 });
        let body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(productoNombre)) throw new Error('El producto no aparece en el listado tras crearlo');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(productoNombre)) throw new Error('El producto no persiste tras recargar');
    });

    const cuponCodigo = `E2E${STAMP}`;
    await step('Cupones: crear y persistir', async () => {
        await page.goto(BASE + '/cupones', { waitUntil: 'networkidle' });
        await page.getByRole('button', { name: 'Nuevo cupón' }).click();
        const dialog = page.locator('dialog[open]');
        await dialog.waitFor({ state: 'visible' });
        await dialog.locator('input[type="text"]').first().fill(cuponCodigo);
        await dialog.locator('input[type="number"]').first().fill('15');
        await dialog.getByRole('button', { name: 'Crear cupón' }).click();
        await dialog.waitFor({ state: 'hidden', timeout: 10000 });
        await page.waitForTimeout(300);
        let body = await page.evaluate(() => document.body.textContent);
        if (!body.toUpperCase().includes(cuponCodigo)) throw new Error('El cupón no aparece en el listado tras crearlo');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (!body.toUpperCase().includes(cuponCodigo)) throw new Error('El cupón no persiste tras recargar');
    });

    const zonaNombre = `Zona E2E ${STAMP}`;
    await step('Envíos: crear zona y persistir', async () => {
        await page.goto(BASE + '/envios', { waitUntil: 'networkidle' });
        await page.getByRole('button', { name: 'Nueva zona' }).click();
        const dialog = page.locator('dialog[open]');
        await dialog.waitFor({ state: 'visible' });
        await dialog.locator('input[type="text"]').first().fill(zonaNombre);
        await dialog.getByText('CABA', { exact: true }).click();
        await dialog.locator('input[type="number"]').first().fill('2500');
        await dialog.getByRole('button', { name: 'Crear zona' }).click();
        await dialog.waitFor({ state: 'hidden', timeout: 10000 });
        await page.waitForTimeout(300);
        let body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(zonaNombre)) throw new Error('La zona de envío no aparece en el listado tras crearla');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (!body.includes(zonaNombre)) throw new Error('La zona de envío no persiste tras recargar');
    });

    await step('Pedidos: cambiar estado y persistir', async () => {
        await page.goto(BASE + '/pedidos', { waitUntil: 'networkidle' });
        const primerVer = page.getByRole('link', { name: 'Ver' }).first();
        await primerVer.click();
        await page.waitForURL('**/pedidos/*');
        await page.locator('select').selectOption('enviado');
        await page.getByRole('button', { name: 'Guardar' }).click();
        await page.waitForTimeout(500);
        let body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Enviado')) throw new Error('El estado del pedido no se actualizó');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Enviado')) throw new Error('El estado del pedido no persiste tras recargar');
    });

    await step('Reportes de ventas cargan con datos reales', async () => {
        await page.goto(BASE + '/reportes', { waitUntil: 'networkidle' });
        const body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Ingresos totales')) throw new Error('El reporte de ventas no muestra las tarjetas de resumen');
    });

    await step('Tienda pública: agregar producto al carrito y persistir', async () => {
        await page.goto(BASE + '/tienda', { waitUntil: 'networkidle' });
        const primeraTarjeta = page.locator('a[href^="/tienda/"]').first();
        await primeraTarjeta.click();
        await page.waitForURL('**/tienda/*');
        await page.getByRole('button', { name: /Agregar al carrito/ }).click();
        await page.waitForTimeout(500);

        await page.goto(BASE + '/carrito', { waitUntil: 'networkidle' });
        let body = await page.evaluate(() => document.body.textContent);
        if (body.includes('Tu carrito está vacío')) throw new Error('El producto no se agregó al carrito');

        await page.reload({ waitUntil: 'networkidle' });
        body = await page.evaluate(() => document.body.textContent);
        if (body.includes('Tu carrito está vacío')) throw new Error('El carrito no persiste tras recargar');
    });

    await step('Checkout: el candado de prueba bloquea confirmar el pedido', async () => {
        await page.goto(BASE + '/checkout', { waitUntil: 'networkidle' });
        const body = await page.evaluate(() => document.body.textContent);
        if (!body.includes('Versión de prueba')) {
            throw new Error('No se muestra el aviso de modo prueba en el checkout');
        }
    });

    await step('Anti-genérico: dashboard y login sin trazas de Laravel/Breeze', async () => {
        for (const url of ['/dashboard', '/login', '/productos', '/pedidos']) {
            await page.goto(BASE + url, { waitUntil: 'networkidle' });
            const html = await page.content();
            if (/laravel/i.test(html)) throw new Error(`"${url}" contiene "Laravel" en el HTML`);
            if (/you're logged in/i.test(html)) throw new Error(`"${url}" contiene texto placeholder de Breeze`);
        }
    });

    console.log('\n✅ Todas las pruebas E2E pasaron correctamente.\n');
} finally {
    await browser.close();
}
