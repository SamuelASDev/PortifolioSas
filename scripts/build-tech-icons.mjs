// Gera resources/data/tech-icons.json com um subconjunto do Simple Icons (CC0).
// Uso: node scripts/build-tech-icons.mjs
import { writeFileSync, mkdirSync } from 'node:fs';
import * as icons from 'simple-icons';

const slugs = `php laravel symfony codeigniter wordpress drupal composer javascript typescript nodedotjs deno bun
react nextdotjs vuedotjs nuxtdotjs openjdk angular svelte astro jquery alpinedotjs livewire inertia htmx vite webpack
html5 css3 css sass less tailwindcss bootstrap bulma materialdesign chakraui styledcomponents
python django flask fastapi pandas numpy jupyter tensorflow pytorch scikitlearn opencv openai anthropic huggingface langchain
java spring springboot kotlin android gradle apachemaven c cplusplus dotnet go rust ruby rubyonrails elixir dart flutter swift ios
mysql postgresql sqlite mariadb mongodb redis firebase supabase prisma graphql apollographql
docker kubernetes nginx apache linux ubuntu debian git github gitlab bitbucket githubactions vercel netlify heroku
digitalocean cloudflare googlecloud hostinger
figma canva adobephotoshop adobeillustrator blender unity unrealengine godotengine threedotjs electron tauri
expo reactrouter redux jest vitest cypress selenium puppeteer postman insomnia swagger
stripe mercadopago paypal shopify woocommerce wix arduino raspberrypi espressif n8n zapier make
discord telegram whatsapp twilio socketdotio rabbitmq apachekafka elasticsearch grafana prometheus
markdown notion trello jira obsidian lua unity`.split(/\s+/).filter(Boolean);

const out = {};
for (const icon of Object.values(icons)) {
    if (icon && typeof icon === 'object' && slugs.includes(icon.slug)) {
        out[icon.slug] = { title: icon.title, hex: icon.hex, path: icon.path };
    }
}
mkdirSync('resources/data', { recursive: true });
writeFileSync('resources/data/tech-icons.json', JSON.stringify(out));
console.log(`${Object.keys(out).length}/${new Set(slugs).size} ícones`, [...new Set(slugs)].filter((s) => !out[s]).join(' '));
