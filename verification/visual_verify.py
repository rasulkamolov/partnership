from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        print("Logging in...")
        page.goto("http://localhost:8080")
        if page.is_visible('input[name="user"]'):
            page.fill('input[name="user"]', "admin_updated")
            page.fill('input[name="pass"]', "newpass123")
            page.click('button[name="login"]')
            page.wait_for_selector('text=Boshqaruv Paneli')

        print("Capturing Settings (Groups)...")
        page.goto("http://localhost:8080/?p=settings")
        page.screenshot(path="verification/settings_groups.png")

        print("Capturing Edit Student...")
        # Find a student edit link
        page.goto("http://localhost:8080/?p=students")
        page.locator('a[href*="edit_student"]').first.click()
        page.wait_for_selector('text=O\'quvchi Ma\'lumotlarini Tahrirlash')
        page.screenshot(path="verification/edit_student_limit.png")

        print("Capturing Reports...")
        page.goto("http://localhost:8080/?p=reports")
        page.screenshot(path="verification/reports_financial.png")

    except Exception as e:
        print(f"Error: {e}")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
