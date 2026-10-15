const hex = (value) => typeof value === 'string' && /^#[\da-f]{6}$/i.test(value);

export function normalizeSettings(input, schema) {
    const appearance = input?.appearance ?? {};
    const colors = Object.fromEntries(Object.entries(appearance.colors ?? {}).filter(([key, value]) => Object.hasOwn(schema.colors, key) && hex(value)));
    return {
        theme: input?.theme === 'light' ? 'light' : 'dark',
        primary_color: hex(input?.primary_color) ? input.primary_color : '#6366f1',
        appearance: {
            font: Object.hasOwn(schema.fonts, appearance.font) ? appearance.font : 'system',
            base_size: [14, 16, 18].includes(Number(appearance.base_size)) ? Number(appearance.base_size) : 16,
            title_scale: [90, 100, 110].includes(Number(appearance.title_scale)) ? Number(appearance.title_scale) : 100,
            colors,
        },
    };
}

export function luminance(color) {
    const rgb = color.slice(1).match(/../g).map((channel) => {
        const value = parseInt(channel, 16) / 255;
        return value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4;
    });
    return .2126 * rgb[0] + .7152 * rgb[1] + .0722 * rgb[2];
}

export function contrast(a, b) {
    const values = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (values[0] + .05) / (values[1] + .05);
}

export function resolvedColors(settings, schema) {
    return { ...schema.palettes[settings.theme], ...settings.appearance.colors };
}

export function applySettings(doc, input, schema) {
    const settings = normalizeSettings(input, schema);
    const colors = resolvedColors(settings, schema);
    const accent = settings.primary_color;
    const styles = {
        ...colors, accent,
        'accent-ink': contrast(accent, '#000000') >= contrast(accent, '#ffffff') ? '#000000' : '#ffffff',
        font: schema.fonts[settings.appearance.font].css,
        'base-size': `${settings.appearance.base_size}px`,
        'title-scale': settings.appearance.title_scale / 100,
    };
    Object.entries(styles).forEach(([key, value]) => doc.body.style.setProperty(`--${key}`, value));
    doc.body.dataset.theme = settings.theme;
    doc.documentElement.style.fontSize = `${settings.appearance.base_size}px`;
    doc.querySelector('meta[name="theme-color"]')?.setAttribute('content', accent);
    return settings;
}
