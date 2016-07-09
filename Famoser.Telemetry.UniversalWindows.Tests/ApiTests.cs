using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Services;
using Microsoft.VisualStudio.TestPlatform.UnitTestFramework;

namespace Famoser.Telemetry.UniversalWindows.Test
{
    [TestClass]
    public class ApiTests
    {
        private const string TestApplicationId = "29685dba-b863-4538-9127-2b4612034fdc";
        private const string UserId = "11";
        [TestMethod]
        public void TestInitialisation()
        {
            FamoserTelemetry.Initialize(TestApplicationId);
        }

        [TestMethod]
        public async Task TestSubmission()
        {
            var service = new RestService();
            var res = await service.PostAsync(new Uri("https://api.telemetry.famoser.ch/1.0/submit"), new[]
            {
                new KeyValuePair<string, string>(),
                new KeyValuePair<string, string>("Version", "1"),
                new KeyValuePair<string, string>("ApplicationId", TestApplicationId),
                new KeyValuePair<string, string>("UserId", UserId),
                new KeyValuePair<string, string>("ContentType", "event"),
                new KeyValuePair<string, string>("Content", "testApi")
            });
            Assert.IsTrue(res.IsRequestSuccessfull);
        }

        [TestMethod]
        public async Task SendSomeLogs()
        {
            FamoserTelemetry.Initialize(TestApplicationId);
            LogHelper.Instance.LogError("error log", this);
            LogHelper.Instance.LogWarning("warning log", this);
            await Task.Delay(10000);
        }
    }
}
