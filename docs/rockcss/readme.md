# RockCSS

RockCSS is a powerful CSS extension that brings fluid typography and responsive scaling to your ProcessWire projects with minimal effort. It introduces two groundbreaking functions - `grow()` and `shrink()` - that make responsive design intuitive and maintainable.

## Why RockCSS is Great

1. **Simplified Fluid Typography**
   - No more complex `calc()` or `clamp()` formulas
   - Intuitive syntax that clearly shows your intent
   - Perfect for creating smooth, responsive text scaling

2. **Smart Defaults**
   - Pre-configured breakpoints (375px to 1440px) that work for most projects
   - Easily customizable when needed
   - Based on real-world usage patterns

3. **Zero Dependencies**
   - Built directly into RockDevTools
   - No external libraries required
   - Lightweight and fast

4. **Developer-Friendly**
   - Clean, readable syntax
   - Predictable output

## Usage

### The grow() Function

The `grow()` function smoothly scales a value UP as the viewport width increases:

```css
.element {
  font-size: grow(16px, 24px);
}
```

This will:
- Start at 16px on mobile (375px viewport)
- Smoothly scale up to 24px on desktop (1440px viewport)
- Create fluid scaling in between
- Maintain min/max limits for viewport sizes outside the range

### The shrink() Function

The `shrink()` function smoothly scales a value DOWN as the viewport width decreases:

```css
.element {
  padding: shrink(100px, 20px);
}
```

This will:
- Start at 100px on desktop (1440px viewport)
- Smoothly scale down to 20px on mobile (375px viewport)
- Create fluid scaling in between
- Maintain min/max limits for viewport sizes outside the range

### The pxrem Feature

The `pxrem` feature converts pixel values to rem units based on your configured `remBase`:

```css
.element {
  padding: 20pxrem;
}
```

This will convert 20px to rem units. For example, if your `remBase` is 20, this would output `1rem`.

### Custom Breakpoints

You can optionally specify custom breakpoints:

```css
.element {
  margin: grow(20px, 40px, 768px, 1920px);
}
```

## Configuration

RockCSS comes with sensible defaults but can be easily customized:

```php
// site/ready.php
$rockcss = rockdevtools()->rockcss();
$rockcss->setup([
  'remBase' => 20, // Change the base rem value
  'min' => 375,    // Change minimum viewport width
  'max' => 1440,   // Change maximum viewport width
]);
```

## How It Works

RockCSS transforms your intuitive `grow()` and `shrink()` functions into optimized CSS `clamp()` statements. For example:

```css
/* Your code */
font-size: grow(16px, 24px);

/* Compiles to */
font-size: clamp(16px, 16px + 8 * ((100vw - 375px) / (1440 - 375)), 24px);
```

## Benefits Over Traditional Methods

1. **Maintainability**
   - Clear intent in your code
   - Easy to understand and modify values
   - No complex calculations to maintain

2. **Reliability**
   - Consistent behavior across browsers
   - No floating-point rounding issues
   - Predictable scaling

3. **Performance**
   - Compiles to native CSS
   - No JavaScript required
   - Zero runtime overhead

4. **DRY (Don't Repeat Yourself)**
   - No need to repeat complex calculations
   - Consistent scaling across your project
   - Easy to update global scaling behavior

## Support

You can not only use RockCSS syntax for font sizes but for any css property that should increase or decrease with screen size:
   - Font sizes
   - Margins and padding
   - Grid gaps
   - etc

## Conclusion

RockCSS brings the power of fluid typography and responsive scaling to ProcessWire in an elegant, maintainable way. It simplifies complex responsive design patterns into intuitive functions that are easy to understand and modify, making it an invaluable tool for modern web development.
