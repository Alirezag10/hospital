"""Check the running local site without creating records.

Usage: python scripts/check-http.py [http://127.0.0.1:8080]
"""
import html.parser
import http.cookiejar
import sys
import urllib.error
import urllib.parse
import urllib.request

base = sys.argv[1].rstrip('/') if len(sys.argv) > 1 else 'http://127.0.0.1:8080'
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
passed = 0
failures = []


def request(path, data=None):
    payload = None if data is None else urllib.parse.urlencode(data).encode()
    try:
        with opener.open(urllib.request.Request(base + path, data=payload), timeout=15) as response:
            return response.status, response.read().decode('utf-8', errors='replace')
    except urllib.error.HTTPError as error:
        return error.code, error.read().decode('utf-8', errors='replace')


def check(label, condition):
    global passed
    if condition:
        passed += 1
        print('PASS', label, flush=True)
    else:
        failures.append(label)
        print('FAIL', label, flush=True)


class Inputs(html.parser.HTMLParser):
    token = None

    def handle_starttag(self, tag, attributes):
        attributes = dict(attributes)
        if tag == 'input' and attributes.get('name') == '_csrf':
            self.token = attributes.get('value')


for path in ['/site/index', '/patient/index', '/patient/create', '/admission/index',
             '/admission/create', '/admission/calendar', '/admission-service/create',
             '/discharge/create', '/doctor/index', '/doctor/create', '/ward/index', '/ward/create']:
    check(path, request(path)[0] == 200)

for controller, form in [('patient', 'PatientSearch'), ('admission', 'AdmissionSearch')]:
    for attribute in ['name', 'national_code', 'mobile']:
        params = urllib.parse.urlencode({f'{form}[{attribute}][]': 'x'})
        check(f'Array filter {controller}/{attribute}', request(f'/{controller}/index?{params}')[0] == 200)
check('Array status filter', request('/admission/index?AdmissionSearch[status][]=x')[0] == 200)
status, body = request('/admission/index?AdmissionSearch[mobile]=00000000000')
check('Mobile filter empty state', status == 200 and 'پذیرشی مطابق جست' in body)

for path in ['/patient/view?id=-1', '/admission/view?id=invalid',
             '/admission/create?patient_id[]=1', '/discharge/create?admission_id=-1',
             '/admission-service/create?admission_id=0']:
    check('Invalid ID ' + path, request(path)[0] == 404)
for path in ['/admission/search-patients?q[]=x', '/admission/search-doctors?q[]=x',
             '/admission/search-open-admissions?q[]=x']:
    status, body = request(path)
    check('Invalid AJAX ' + path, status == 200 and body.strip() == '[]')
check('Missing CSRF token', request('/patient/create', {'Patient[first_name]': 'invalid'})[0] == 400)

for controller, model, field in [('patient', 'Patient', 'first_name'), ('doctor', 'Doctor', 'name'),
                                  ('ward', 'Ward', 'name'), ('admission', 'Admission', 'patient_id'),
                                  ('admission-service', 'AdmissionService', 'quantity'), ('discharge', 'Discharge', 'description')]:
    # Discharge has a POST form only after selecting an open admission. Obtain a
    # session token from patient/create instead; the default token is shared.
    _, body = request('/patient/create' if controller == 'discharge' else f'/{controller}/create')
    parser = Inputs()
    parser.feed(body)
    check('CSRF token present ' + model, bool(parser.token))
    for fields in [{f'{model}[{field}][]': 'x'}, {model: 'x'}]:
        check('Malformed POST ' + model, request(f'/{controller}/create', {'_csrf': parser.token, **fields})[0] == 400)

for path in ['/css/site.css', '/css/shamsi.css', '/js/shamsi.js', '/js/patient-search.js',
             '/js/confirm-dialog.js', '/js/ui-enhancements.js', '/fonts/Yekan.woff2']:
    check('Asset ' + path, request(path)[0] == 200)

print(f'{passed} passed, {len(failures)} failed', flush=True)
sys.exit(bool(failures))
