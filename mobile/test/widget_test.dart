import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/main.dart';

void main() {
  testWidgets('App should load without crashing', (WidgetTester tester) async {
    await tester.pumpWidget(const HSEApp());
    expect(find.byType(HSEApp), findsOneWidget);
  });
}
